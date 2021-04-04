<?php
	class admin_functions{
		public $data = array();
		public $tempData = array();
		function collect_accounts($sklad){
			$sqlGetAcc = "";
			require("config.php");
			if($sklad == 0){
				$sqlGetAcc = "SELECT * FROM account";
			}else{
				$sqlGetAcc = "SELECT * FROM account WHERE sklad='" . $sklad . "'";
			}
			
			$result = $conn->query($sqlGetAcc);
			if($result->num_rows > 0){
				foreach($result as $account){
					$token = $account["token"];
					$tokenFin;
					if(!empty($token)){
						$tokenFin = $token;
					}else{
						$tokenFin = "NONE";
					}
					$this->tempData = array(
						"acc_id" => $account["id"],
						"username" => $account["username"],
						"name" => $account["name"],
						"s_name" => $account["s_name"],
						"rank" => $account["rank"],
						"sklad" => $account["sklad"],
						"token" => $tokenFin
					);
					array_push($this->data,$this->tempData); 
				}
			}
            return json_encode($this->data,JSON_UNESCAPED_UNICODE);
		}
		function delete_account($acc_id){
			$this->tempData = array(
				"RESPONSE" => "Acc with id [" . $acc_id ."] has ben deleted !!!"
			);
			array_push($this->data,$this->tempData);
			return json_encode($this->data,JSON_UNESCAPED_UNICODE);
		}
		
		function edit_account($acc_id,$username,$f_name,$s_name,$rank,$sklad){
			$this->tempData = array(
				"RESPONSE" => "Acc with id [" . $acc_id ."] has ben edited !!! <" . $username .",". $f_name .",". $s_name ."," . $rank . ",". $sklad .">"
			);
			array_push($this->data,$this->tempData);
			return json_encode($this->data,JSON_UNESCAPED_UNICODE);
		}
	}
?>