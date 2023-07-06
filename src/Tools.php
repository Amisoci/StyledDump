<?php
	namespace Amisoci;
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
			$ellipsis->set("style","display:none;cursor:pointer;");
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
			echo "<div><i>".$backtrace["file"]." - ".$backtrace["line"]."</i><br>".self::operation($data)."</div><br><br>";
		}
	}
?>
