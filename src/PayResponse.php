<?php


namespace Payment\PaymentSdk;
use Illuminate\Database\Eloquent\Model;
/**
 * 处理返回数据
 */
class PayResponse extends Model
{

    /**
     * 返回一个数据字典
     *
     * @param [type] $data
     * @author wind <254044378@qq.com>
     */
    protected function fromModel($data)
    {
        $data = json_decode($data, true);
        $this->code = $data['code'] ?? '400';
        if ($this->code == '400') {
            $this->error = $data['msg'] ?? '未知错误';
        }
        /*捕捉其他返回错误*/
        if ($this->code < 500 && $this->code > 400) {
            $msg = $data['msg'] ?? '';
            if (empty($msg)) {
                $msg = $data['message'] ?? '';
            }
            $this->error = $msg ?? '未知错误';
        }
        if (!empty($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $key => $value) {
                $this->$key = $value;
            }
        }
        return $this;
    }
}   