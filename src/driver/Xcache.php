<?php

namespace WangGe\Cache\driver;

/**
 * @author WangGe technical department <869476711@qq.com>
 * @desc   Xcache缓存驱动
 * @date 2024-05-02
 */
class Xcache extends Base

{
    protected $options = [
        'prefix' => '',
        'expire' => 0,
    ];

    /**
     * 构造函数
     * @param array $options 缓存参数
     * @access public
     * @throws \BadFunctionCallException
     */
    public function __construct($options = [])
    {
        if (!function_exists('xcache_info')) {
            throw new \BadFunctionCallException('not support: Xcache');
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
        return xcache_isset($key);
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
        return xcache_isset($key) ? xcache_get($key) : $default;
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
        if ($this->tag && !$this->has($name)) {
            $first = true;
        }
        $key = $this->getCacheKey($name);
        if (xcache_set($key, $value, $expire)) {
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
        return xcache_inc($key, $step);
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
        return xcache_dec($key, $step);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name)
    {
        return xcache_unset($this->getCacheKey($name));
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
            // 指定标签清除
            $keys = $this->getTagItem($tag);
            foreach ($keys as $key) {
                xcache_unset($key);
            }
            $this->rm('tag_' . md5($tag));
            return true;
        }
        if (function_exists('xcache_unset_by_prefix')) {
            return xcache_unset_by_prefix($this->options['prefix']);
        } else {
            return false;
        }
    }
}