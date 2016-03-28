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
$GLOBALS['functions'] = array();
$GLOBALS['father'] = FIRST_TAG;
$GLOBALS['compiled'] = array();
$GLOBALS['out']     = "";
$GLOBALS['debug'] = array();

/**
 * Class HTML6_Exception
 */
class HTML6_Exception extends Exception{
    /**
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    function __construct($message = "", $code = 0, Exception $previous = null){
        parent::__construct($message,$code,$previous);
        /**
         * var_dump($GLOBALS['debug'][0]['traceO'][0]['line']);
         */
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
 * @param $name
 * @param $function
 * @return bool
 * @throws HTML6_Exception
 */
function HTML6_add_tag($name,$function){
    $name = strtolower($name);
    if(!isset($GLOBALS['functions'][$name])){
        $GLOBALS['functions'][$name] = $function;
        return true;
    }else{
        throw new HTML6_Exception("The tag '$name' was created before.");
    }
}

/**
 * Load tags
 */
$files = glob("./tags/*.tag.php");
foreach($files as $file){
    include($file);
}

/**
 * @param $start
 * @param $end
 * @param bool|true $isPHP
 * @return array
 */
function HTML6_output_creator($start,$end,$isPHP = true){
    $s = ($isPHP) ? "<?php " : "";
    $e = ($isPHP) ? " ?>" : "";
    return [
        "start" => $s.$start.$e,
        "end"   => $s.$end.$e
    ];
}

/**
 * @param $attributes
 * @return string
 */
function attributes_reader($attributes){
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
function start_handler($xml,$tag,$attributes){
    $tag = strtolower($tag);
    if(isset($GLOBALS['functions'][$tag])){
        $out = $GLOBALS['functions'][$tag]($attributes,$GLOBALS['father']);
        $GLOBALS['compiled'][$tag] = $out;
        $GLOBALS['out'] .= $out['start']."";
        $GLOBALS['father'] = $tag;
    }else{
        $attr = attributes_reader($attributes);
        $GLOBALS['out'] .= "<$tag"."$attr>";
    }
}

/**
 * @param $xml
 * @param $tag
 */
function end_handler($xml,$tag){
    $tag = strtolower($tag);
    if(isset($GLOBALS['functions'][$tag])){
        $out = $GLOBALS['compiled'][$tag];
        $GLOBALS['out'] .= $out['end']."";
        $GLOBALS['father'] = $tag;
    }else{
        $GLOBALS['out'] .= "</$tag>";
    }
}

/**
 * @param $xml
 * @param $data
 */
function character_handler($xml,$data){
    $GLOBALS['out'] .= $data;
}

/**
 * Class HTML6
 */
class HTML6{
    /**
     * @var null
     */
    private $file = null;

    /**
     * @param $file
     */
    public function __construct($file){
        $this->file = $file;
        $this->parser();
    }

    /**
     * Parse and make resualt
     */
    public function parser(){
        $parser = xml_parser_create('UTF-8');
        xml_set_element_handler($parser,'start_handler','end_handler');
        xml_set_character_data_handler($parser,"character_handler");
        xml_parse($parser,file_get_contents($this->file));
    }
}
new HTML6("sample/index.xml");
eval("?>".$GLOBALS['out']);