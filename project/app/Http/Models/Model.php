<?php

/**
 * 模型基类
 * 
 * @author Sharky
 *
 */
declare (strict_types = 1);

namespace App\Http\Models;

use Sharky\DataBase\Model as BaseModel;

abstract class Model extends BaseModel
{
    /** 覆盖自动推断的表名（不含前缀） */
    public const TABLE = null;

    /** 覆盖配置文件的表前缀 */
    public const PREFIX = null;

}
