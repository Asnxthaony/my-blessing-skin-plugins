<?php

namespace Asnxthaony\Smrz\Models;

use DateTimeInterface;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class RealUser extends Model
{
    protected $table = 'realname_info';

    public const PENDING = 0;
    public const ACCEPTED = 1;
    public const REJECTED = 2;

    protected $fillable = [
        'user_id', 'realname', 'id_card',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'state' => 'integer',
    ];

    public function getRealname()
    {
        try {
            $realname = Crypt::decryptString($this->realname);

            $length = mb_strlen($realname) - 1;

            if ($length != 0) {
                return str_repeat('*', $length).mb_substr($realname, $length);
            } else {
                return '*';
            }
        } catch (DecryptException $e) {
            abort(500, '解密敏感信息失败');
        }
    }

    public function getIdCard()
    {
        try {
            $id_card = Crypt::decryptString($this->id_card);

            return substr_replace($id_card, '****************', 1, 16);
        } catch (DecryptException $e) {
            abort(500, '解密敏感信息失败');
        }
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
