<?php

namespace Rho\CircuitBreaker;

use Rho;
use Rho\Exception\CircuitBreakerOpenException;
use \Predis;

class RedisPercentCircuitBreaker extends AbstractPercentCircuitBreaker {
    use Rho\HasLogger;
    use RedisConstructor;

    protected $redisLuaDefined = false;

    public static function wrap($obj, $opts = []) {
        return new RedisPercentCircuitBreaker($obj, $opts);
    }

    public function __call($name, $args) {
        switch($this->circuitState()) {
            case self::CLOSED:
                $this->_logger()->debug("circuit closed", ['func' => $name]);
                try {
                    return call_user_func_array([$this->obj, $name], $args);
                } catch (\Exception $e) {
                    $this->_logger()->error('Exception', ['exception' => $e]);
                    $this->circuitRecordFail();
                    throw $e;
                }
                break;
            case self::OPEN:
                $this->_logger()->warning("circuit open", ['func' => $name]);
                throw new CircuitBreakerOpenException();
                break;
        }
    }

    protected function circuitState() {
        if(!$this->redisLuaDefined) {
            $this->redis->getProfile()->defineCommand('circuitstate', 'Rho\CircuitBreaker\RedisCircuitStateCommand');
            $this->redisLuaDefined = true;
        }

        return $this->redis->circuitstate(
            $this->prefix . 'calls',
            $this->prefix . 'fails',
            $this->prefix . 'start',
            time(),
            $this->timePeriod,
            $this->minCalls,
            $this->failThreshold
        );
    }

    public function circuitRecordFail() {
        $fails = $this->redis->incr($this->prefix . 'fails');
        $this->_logger()->warning("circuit failed", ['fails' => $fails]);
    }
}

class RedisCircuitStateCommand extends Predis\Command\ScriptCommand {
    public function getKeysCount() {
        return 3;
    }

    public function getScript() {
        return <<<LUA
local now = tonumber(ARGV[1])
local timePeriod = tonumber(ARGV[2])
local minCalls = tonumber(ARGV[3])
local failThreshold = tonumber(ARGV[4])
local callsKey = KEYS[1]
local failsKey = KEYS[2]
local periodKey = KEYS[3]
local calls = tonumber(redis.call('GET', callsKey) or 0)
local fails = tonumber(redis.call('GET', failsKey) or 0)
local periodStart = tonumber(redis.call('GET', periodKey) or 0)
local result = 0 -- default closed

if now - periodStart >= timePeriod then
    redis.call('SET', periodKey, now)
    redis.call('SET', callsKey, 0)
    redis.call('SET', failsKey, 0)
end

if calls >= minCalls and fails / calls >= failThreshold then
   result = 1 -- open
end

redis.call('INCR', callsKey)

return result
LUA;
    }
}
