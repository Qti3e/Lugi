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
HTML6_add_tag("if",function($params){
    $query = $params['QUERY'];
   return HTML6_output_creator(
       "if($query){",
       "}"
   );
});
HTML6_add_tag("elseif",function($params,$father){
    $query = $params['QUERY'];
    if($father == "if") {
        return HTML6_output_creator("}elseif($query)","");
    }
    return "";
});
HTML6_add_tag("else",function($params,$father){
   if($father == "if" || $father == "elseif") {
        return HTML6_output_creator("}else{","");
   }
    return "";
});
HTML6_add_tag("var",function($params){
    $re = "";
    foreach($params as $n=>$value){
        $n = strtolower($n);
        $re .= "$$n = '$value';";
    }
    return HTML6_output_creator($re,"");
});
HTML6_add_tag("while",function($params){
    $query = $params['QUERY'];
   return HTML6_output_creator("while($query){","}");
});
HTML6_add_tag("php",function(){
    return HTML6_output_creator("<?php","?>",false);
});