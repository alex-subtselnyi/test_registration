<?php

class AjaxRequest
{
    public $actions = [];

    public $data;
    public $code;
    public $message;
    public $status;
    public $return;

    public function __construct($request)
    {
        $this->request = $request;
        $this->action = $this->getRequestParam("act");

        if (!empty($this->actions[$this->action])) {
            $this->callback = $this->actions[$this->action];
            call_user_func([$this, $this->callback]);
        } else {
            header("HTTP/1.1 400 Bad Request");
            $this->setFieldError("main", "Некорректный запрос");
        }

        $this->response = $this->renderToString();
    }



    public function getRequestParam($name)
    {
        if (array_key_exists($name, $this->request)) {
            return trim($this->request[$name]);
        }
        return null;
    }

    public function checkImage($name) {
        if ($_FILES[$name]['name']) {
            return true;
        }
        return false;
    }

    public function storeImage($name)
    {
        $image_name = $_FILES[$name]['name'];
        $image_temp = $_FILES[$name]['tmp_name'];
        $image_path = "/assets/files/";

        $type=['jpg','jpeg','png','gif'];
        $ext = explode(".",$image_name);
        if(!(in_array($ext[1],$type)))
        {
            $this->setFieldError("file", "Type does not match");
            return false;
        }
        if(is_uploaded_file($image_temp)) {
            if(move_uploaded_file($image_temp, $_SERVER['DOCUMENT_ROOT'] . $image_path . $image_name)) {
                return $image_path . $image_name;
            } else {
                $this->setFieldError("file", "Cant save file");
                return false;
            }
        }
        else {
            $this->setFieldError("file", "No file");
            return false;
        }
    }


    public function setResponse($key, $value)
    {
        $this->data[$key] = $value;
    }


    public function setFieldError($name, $message = "", $return = "index")
    {
        $this->status = "err";
        $this->code = $name;
        $this->message = $message;
        $this->return = $return;
    }


    public function renderToString()
    {
        $this->json = [
            "status" => $this->status,
            "code" => $this->code,
            "message" => $this->message,
            "return" => $this->return,
            "data" => $this->data,
        ];
        return json_encode($this->json, ENT_NOQUOTES);
    }


    public function redirectResponse()
    {
        $data = json_decode($this->response);
        if ($data->status === 'err')
        {
            include ($data->return . ".php");
        } else {
            $redirect = $data->data->redirect;
            header("Location: $redirect");
        }
    }
}
