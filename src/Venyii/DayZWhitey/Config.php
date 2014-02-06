<?php

/*
 * This file is part of DayZWhitey
 *
 * (c) venyii <ven@cersei.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Venyii\DayZWhitey;

class Config implements \ArrayAccess
{
    /**
     * @var array
     */
    private $data;

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     *                         An offset to check for.
     *                         </p>
     * @return boolean true on success or false on failure.
     *                        </p>
     *                        <p>
     *                        The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     *                       The offset to retrieve.
     *                       </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     *                       The offset to assign the value to.
     *                       </p>
     * @param mixed $value <p>
     *                       The value to set.
     *                       </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     *                       The offset to unset.
     *                       </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * @param \SplFileInfo $iniFile
     */
    public function fromIni(\SplFileInfo $iniFile)
    {
        foreach (parse_ini_file($iniFile->getRealPath(), true) as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

}
