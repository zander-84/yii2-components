<?php


namespace zander84\helpers\rewrite;

use Yii;

class RedisMutex extends \yii\redis\Mutex
{

    public $externalVal = 'dashboard';

    /**
     * Generates a unique key used for storing the mutex in Redis.
     *
     * @param string $name mutex name.
     *
     * @return string a safe cache key associated with the mutex name.
     */
    protected function calculateKey ($name)
    {

        return $name;
    }

    /**
     * Acquires a lock by name.
     *
     * @param string $name of the lock to be acquired. Must be unique.
     * @param int $timeout time (in seconds) to wait for lock to be released. Defaults to zero meaning that method will return
     * false immediately in case lock was already acquired.
     *
     * @return bool lock acquiring result.
     */
    public function acquireExternal ($name, $timeout = 0)
    {
        if ($val = $this->acquireLockExternal($name, $timeout)) {

            return $val;
        }

        return false;
    }

    /**
     * Releases acquired lock. This method will return false in case the lock was not found.
     *
     * @param string $name of the lock to be released. This lock must already exist.
     *
     * @return bool lock release result: false in case named lock was not found..
     */
    public function releaseExternal ($name)
    {
        if ($this->releaseLockExternal($name)) {

            return true;
        }

        return false;
    }

    /**
     * Acquires a lock by name.
     *
     * @param string $name of the lock to be acquired. Must be unique.
     * @param int $timeout time (in seconds) to wait for lock to be released. Defaults to `0` meaning that method will return
     * false immediately in case lock was already acquired.
     *
     * @return bool lock acquiring result.
     */
    protected function acquireLockExternal ($name, $timeout = 0)
    {
        $key = $this->calculateKey($name);
        $value = $this->externalVal;
        $waitTime = 0;
        while (!$this->redis->executeCommand('SET', [$key, $value, 'NX', 'PX', (int)($this->expire * 1000)])) {
            $waitTime++;
            if ($waitTime > $timeout) {
                return false;
            }
            sleep(1);
        }
        return true;
    }

    /**
     * Releases acquired lock. This method will return `false` in case the lock was not found or Redis command failed.
     *
     * @param string $name of the lock to be released. This lock must already exist.
     *
     * @return bool lock release result: `false` in case named lock was not found or Redis command failed.
     */
    protected function releaseLockExternal ($name)
    {
        static $releaseLuaScript = <<<LUA
if redis.call("GET",KEYS[1])==ARGV[1] then
    return redis.call("DEL",KEYS[1])
else
    return 0
end
LUA;
        if (!$this->redis->executeCommand('EVAL', [
            $releaseLuaScript,
            1,
            $this->calculateKey($name),
            $this->externalVal
        ])) {
            return false;
        } else {
            unset($this->_lockValues[$name]);
            return true;
        }
    }


}
