<?php

namespace WangGe\Cache\driver;

/**
 * @author WangGe technical department <869476711@qq.com>
 * @desc   Wincache缓存驱动
 * @date 2024-05-02
 */
class Wincache extends Base

{
    protected $options = [
        'prefix' => '',
        'expire' => 0,
    ];

    /**
     * 构造函数
     * @param array $options 缓存参数
     * @throws \BadFunctionCallException
     * @access public
     */
    public function __construct($options = [])
    {
        if (!function_exists('wincache_ucache_info')) {
            throw new \BadFunctionCallException('not support: WinCache');
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
    }

    /**
     * 判断缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name)
    {
        $key = $this->getCacheKey($name);
        return wincache_ucache_exists($key);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $key = $this->getCacheKey($name);
        return wincache_ucache_exists($key) ? wincache_ucache_get($key) : $default;
    }

    /**
     * 写入缓存
     * @access public
     * @param string            $name 缓存变量名
     * @param mixed             $value  存储数据
     * @param integer|\DateTime $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null)
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        if ($expire instanceof \DateTime) {
            $expire = $expire->getTimestamp() - time();
        }
        $key = $this->getCacheKey($name);
        if ($this->tag && !$this->has($name)) {
            $first = true;
        }
        if (wincache_ucache_set($key, $value, $expire)) {
            isset($first) && $this->setTagItem($key);
            return true;
        }
        return false;
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1)
    {
        $key = $this->getCacheKey($name);
        return wincache_ucache_inc($key, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1)
    {
        $key = $this->getCacheKey($name);
        return wincache_ucache_dec($key, $step);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name)
    {
        return wincache_ucache_delete($this->getCacheKey($name));
    }

    /**
     * 清除缓存
     * @access public
     * @param string $tag 标签名
     * @return boolean
     */
    public function clear($tag = null)
    {
        if ($tag) {
            $keys = $this->getTagItem($tag);
            foreach ($keys as $key) {
                wincache_ucache_delete($key);
            }
            $this->rm('tag_' . md5($tag));
            return true;
        } else {
            return wincache_ucache_clear();
        }
    }

}