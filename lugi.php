<?php
/*****************************************************************************
 *         In the name of God the Most Beneficent the Most Merciful          *
 *___________________________________________________________________________*
 *   This program is free software: you can redistribute it and/or modify    *
 *   it under the terms of the GNU General Public License as published by    *
 *   the Free Software Foundation, either version 3 of the License, or       *
 *   (at your option) any later version.                                     *
 *___________________________________________________________________________*
 *   This program is distributed in the hope that it will be useful,         *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of          *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           *
 *   GNU General Public License for more details.                            *
 *___________________________________________________________________________*
 *   You should have received a copy of the GNU General Public License       *
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.   *
 *___________________________________________________________________________*
 *                       Created by AliReza Ghadimi                          *
 *     <http://AliRezaGhadimi.ir>    LO-VE    <AliRezaGhadimy@Gmail.com>     *
 *****************************************************************************/
define("FIRST_TAG",md5("first_tag"));
$GLOBALS['debug'] = array();

/**
 * Class Lugi_Exception
 */
class Lugi_Exception extends Exception{
    /**
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    function __construct($message = "", $code = 0, Exception $previous = null){
        parent::__construct($message,$code,$previous);
        $GLOBALS['debug'][] =  [
            "line" => $this->getLine(),
            "code" => $this->getCode(),
            "file" => $this->getFile(),
            "msg"  => $this->getMessage(),
            "trace" => $this->getTraceAsString(),
            "traceO" => $this->getTrace()
        ];
    }
}

/**
 * Class Lugi
 */
class Lugi{
    /**
     * @var bool
     */
    private $debug  = false;

    /**
     * @var array
     */
    private $vars    = array();

    /**
     * @var string
     */
    private $php_code = "";

    /**
     * @var string
     */
    private $compile_folder = "./compiled_tpl";

    /**
     * @var array
     */
    private $tags = array();

    /**
     * @var string
     */
    private $father = FIRST_TAG;

    /**
     * @var array
     */
    private $tagsUsed = array();

    /**
     * @param bool|false $debug
     */
    public function __construct($debug = false){
        $this->debug = $debug;
        $this->tags  = $this->tags();
    }

    /**
     * @return array
     */
    private function tags(){
        $tags = array();
        return $tags;
    }

    /**
     * @param $name
     * @param $value
     */
    public function assign($name,$value){
        $this->vars[$name] = $value;
    }

    /**
     * @param $file
     */
    public function display($file){
        if(file_exists($this->compile_folder)){
            if(!is_dir($this->compile_folder)){
                mkdir($file);
            }
        }else{
            mkdir($file);
        }
        $compiled__file = $this->compile_folder."/".md5_file($file).".tpl.php";
        if(file_exists($compiled__file)){
            include $this->compile_folder."/".md5_file($file).".tpl.php";
        }else{
            $data = file_get_contents($file);
            $this->parser($data);
            file_put_contents($compiled__file,$this->php_code);
            eval("?>".$this->php_code);
        }
    }

    /**
     * @param $attributes
     * @return string
     */
    private function attributes_reader($attributes){
        $re = "";
        foreach($attributes as $name=>$attribute){
            $re .= " $name='$attribute'";
        }
        return $re;
    }

    /**
     * @param $xml
     * @param $tag
     * @param $attributes
     */
    private function start_handler($xml,$tag,$attributes){
        $tag = strtolower($tag);
        if($tag[0] == "$"){
            $attributes["name"] = substr($tag,1,strlen($tag));
            $tag = "echo";
        }

        if(isset($this->tags[$tag])){
            $out = $this->tags[$tag]($attributes,$this->father);
            $this->tagsUsed[$tag] = $out;
            $this->php_code .= $out["start"];
        }else{
            $attr = $this->attributes_reader($attributes);
            $this->php_code .= "<$tag"."$attr>";
        }
        $this->father = $tag;
    }

    /**
     * @param $xml
     * @param $tag
     */
    private function end_handler($xml,$tag){
        $tag = strtolower($tag);
        if(isset($this->tags[$tag])){
            $this->php_code .= $this->tagsUsed[$tag]["end"];
        }else{
            $this->php_code .= "</$tag>";
        }
    }

    /**
     * @param $xml
     * @param $data
     */
    function character_handler($xml,$data){
        $this->php_code .= $data;
    }

    /**
     * @param $data
     */
    private function parser($data){
        $xml = xml_parser_create("UTF-8");
        xml_set_element_handler($xml,array($this,"start_handler"),array($this,"end_handler"));
        xml_set_character_data_handler($xml,array($this,"character_handler"));
        xml_parse($xml,$data);
    }

    /**
     * @param $name
     * @return string
     */
    private function GetEqOfVarInPHP($name){
        $re = "";
        $e = explode(".",$name);
        foreach($e as $i=>$l){
            if($l[0] == "$"){
                $s = ($i !== 0) ? "[" : "";
                $e = ($i !== 0) ? "]" : "";
                $l = $s.'$this->vars["'.substr($l,1,strlen($l)).'"]'.$e;
            }else{
                $l = '["'.$l.'"]';
            }
            $re .= $l;
        }
        return $re;
    }
}
$test = new Lugi();
$test->assign("test","AliReza");
$test->display("test.tpl");