<?php
	namespace Amisoci;
	$speed_items = array();
	class Element {
		private $attribute = [];
		private $element="";
		private $innerHTML="";
		function __construct($element){
			$this->element = $element;
		}
		function set($name,$value){
			$this->attribute[$name] = str_replace("\t","",str_replace("\n","",$value));
		}
		function innerHTML($text){
			$this->innerHTML=$text;
		}
		function draw(){
			$text = "<".$this->element;
			foreach($this->attribute as $name=>$value){
				$text .= " ".$name."=\"".$value."\"";
			}
			$text.=">".$this->innerHTML."</".$this->element.">";
			return $text;
		}
	}
	class Tools{
		private static function getLastItem(){
			$last_item = end($GLOBALS["speed_items"]);
			return is_array($last_item)?self::getLastItem($last_item):$last_item;
		}
		public static function speed($text="",$loop_data=false){
			if($text==""){
				if(count($GLOBALS["speed_items"])>0){
					$time_array = array();
					foreach($GLOBALS["speed_items"] as $name=>$data){
						if(is_array($data)){
							$time_array[$name] = array();
							foreach($data as $index=>$times){
								$time_array[$name][$index] = $times->interval;
							}
						} else {
							$time_array[$name] = $data->interval;
						}
					}
					self::dump($time_array);
				} else {
					$object = new \stdClass();
					$object->time = hrtime(true);
					$object->interval = 0;
					$GLOBALS["speed_items"]=array($object);
				}
			} else {
				$time = hrtime(true);
				$last_item = self::getLastItem();
				$seconds = floor(($time-$last_item->time)/1e+9);
				if(is_string($loop_data)){
					
				} elseif(is_array($loop_data)){
					
				} else {
					
				}
				self::dump($last_item);
				exit;
				/*if($loop_data!==false){
					if(!isset($GLOBALS["speed_items"][$text])){
						$seconds = 0;
					} else {
						$data = $GLOBALS["speed_items"][$text];
						$next_index = 0;
						while(is_array($data)){
							$data = $data[$loop_data[$next_index++]];
						}
						$seconds = floor(($time-end($GLOBALS["speed_items"][$text])->time)/1e+9);
					}
				} else {
					$seconds = floor(($time-end($GLOBALS["speed_items"])->time)/1e+9);
				}
				if(is_array(end($GLOBALS["speed_items"]))){
					$last_item = end($GLOBALS["speed_items"]);
					$last_time = end($last_item)->time;
				} else {
					$last_time = end($GLOBALS["speed_items"])->time;
				}
				$milliseconds = floor((($time-$last_time)%1e+9)/1e+6);
				$microseconds = floor((($time-$last_time)%1e+6)/1e+3);
				$nanoseconds = floor(($time-$last_time)%1e+3);
				$interval_string = ($seconds>0?$seconds."s":"");
				$interval_string .= ($milliseconds>0?($interval_string!=""?", ":"").$milliseconds."ms":"");
				$interval_string .= ($microseconds>0?($interval_string!=""?", ":"").$microseconds."μs":"");
				$interval_string .= ($nanoseconds>0?($interval_string!=""?", ":"").$nanoseconds."ns":"");
				
				$object = new \stdClass();
				$object->time = $time;
				$object->interval = $interval_string;
				if($loop_data!==false){
					$GLOBALS["speed_items"][$text][$loop_data]=$object;
				} else {
					$GLOBALS["speed_items"][$text]=$object;
				}*/
			}
		}
		
		private static function expandArrows(){
			$text = "";				
			$down_arrow = new Element("div");
			$down_arrow->set("style","
				display:inline-block;
				width:0;height:0;
				border-left:5px solid transparent;
				border-right:5px solid transparent;
				border-top:5px solid green;
				cursor:pointer;
			");
			$down_arrow->set("class","amisoci-down-arrow");
			$down_arrow->set("onclick","
				if(window.event.shiftKey){
					this.parentElement.querySelectorAll('.amisoci-down-arrow, .amisoci-expand-content').forEach(function(element){
						element.style.display='none';
					});
					this.parentElement.querySelectorAll('.amisoci-right-arrow, .amisoci-ellipsis').forEach(function(element){
						element.style.display='inline-block';
					});
				} else {
					this.style.display='none';
					this.parentElement.querySelector('.amisoci-expand-content').style.display='none';
					this.parentElement.querySelector('.amisoci-right-arrow').style.display='inline-block';
					this.parentElement.querySelector('.amisoci-ellipsis').style.display='inline-block';
				}
			");
			$text .= $down_arrow->draw();
			
			$right_arrow = new Element("div");
			$right_arrow->set("style","
				display:inline-block;
				width:0;height:0;
				border-top:5px solid transparent;
				border-bottom:5px solid transparent;
				border-left:5px solid green;
				cursor:pointer;
				display:none;
			");
			$right_arrow->set("class","amisoci-right-arrow");
			$right_arrow->set("onclick","
				if(window.event.shiftKey){
					this.parentElement.querySelectorAll('.amisoci-right-arrow, .amisoci-ellipsis').forEach(function(element){
						element.style.display='none';
					});
					this.parentElement.querySelectorAll('.amisoci-down-arrow').forEach(function(element){
						element.style.display='inline-block';
					});
					this.parentElement.querySelectorAll('.amisoci-expand-content').forEach(function(element){
						element.style.display='inline';
					});
				} else {
					this.style.display='none';
					this.parentElement.querySelector('.amisoci-ellipsis').style.display='none';
					this.parentElement.querySelector('.amisoci-down-arrow').style.display='inline-block';
					this.parentElement.querySelector('.amisoci-expand-content').style.display='inline';
				}
			");
			$text .= $right_arrow->draw();
			
			$ellipsis = new Element("span");
			$ellipsis->set("style","display:none;cursor:pointer;user-select: none;");
			$ellipsis->set("class","amisoci-ellipsis");
			$ellipsis->innerHTML("...");
			$text .= $ellipsis->draw();
			
			return $text;
		}
		private static function operation($data,$indent=1){
			$text = "";
			if(is_array($data)){
				$text .= "array(".count($data).") [";
				
				$text .= self::expandArrows();
				
				$text .= "<span class='amisoci-expand-content'>
						<div>";
				$glue = "";
				for($i=0;$i<$indent;$i++){
					$glue .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				}
				$text .= $glue.implode(",</div><div>".$glue,array_map(function($k,$v) use($indent){
					if(is_int($k)){
						$html=$k;
					} else {
						$array_key_element = new Element("span");
						$array_key_element->set("style","color:HSLA(0,80%,50%,1)");
						$array_key_element->innerHTML("\"".$k."\"");
						$html=$array_key_element->draw();
					}
					return $html." => ".self::operation($v,$indent+1);
				},array_keys($data),array_values($data)))."</div>";
				for($i=1;$i<$indent;$i++){
					$text .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				}
				$text .= "</span>]";
			} elseif(is_string($data)){
				$text .= "string(".strlen($data).") ";
				$string_element = new Element("span");
				$string_element->set("style","color:HSLA(140,50%,50%,1)");
				$string_element->innerHTML("\"".$data."\"");
				$text.=$string_element->draw();
			} elseif(is_bool($data)){
				$text .= "bool(".($data?"true":"false").")";
			} elseif(is_int($data)){
				$text .= "int(";
				$int_element = new Element("span");
				$int_element->set("style","color:HSLA(230,70%,50%,1)");
				$int_element->innerHTML($data);
				$text.=$int_element->draw();
				$text.=")";
			} elseif(is_float($data)){
				$text .= "float(";
				$float_element = new Element("span");
				$float_element->set("style","color:HSLA(300,50%,50%,1)");
				$float_element->innerHTML($data);
				$text.=$float_element->draw();
				$text.=")";
			} elseif(is_object($data)){
				$array_object = get_object_vars($data);
				$text .= "object(".get_class($data).") <sub>".count($array_object)."</sub> {".self::expandArrows()."<span class='amisoci-expand-content'><div>";
				$glue = "";
				for($i=0;$i<$indent;$i++){
					$glue .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				}
				$text .= $glue.implode(",</div><div>".$glue,array_map(function($k,$v) use($indent){
					if(is_int($k)){
						$html = $k;
					} else {
						$object_key_element = new Element("span");
						$object_key_element->set("style","color:HSLA(0,80%,50%,1)");
						$object_key_element->innerHTML("\"".$k."\"");
						$html = $object_key_element->draw();
					}
					return $html." => ".self::operation($v,$indent+1);
					},array_keys($array_object),array_values($array_object)))."</div>";
				for($i=1;$i<$indent;$i++){
					$text .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				}
				$text .= "</span>}";
			} elseif(is_null($data)){
				$text .= "NULL";
			} else {
				$text .= json_encode($data);
			}
			return $text;
		}
		public static function dump($data){
			$backtrace = debug_backtrace()[0];
			echo "<i>".$backtrace["file"]." - ".$backtrace["line"]."</i><br>".self::operation($data)."<br><br>";
		}
	}
?>