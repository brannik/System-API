<?php
    class functions{
        
        public $data = array();
        public $tempData = array();
        public $accounts = array();
        public $realName = "NONE";
        public $skladNames = array("Неизвестен","Първи","Втори","Трети","Четвърти","Победа");
        public $docStatus = array("0","1");
        function logIn($id,$month){
            require("config.php");
            $sql = "SELECT * FROM account WHERE device_id='" . $id . "'";
            $result = $conn->query($sql);
            if($result->num_rows > 0){
				$mesec = $month + 1;
				
                $row = $result->fetch_assoc();
				$sqlGetCount = "SELECT * FROM documents WHERE MONTH(date) = '" . $mesec. "' AND owner_id='" . $row["id"] . "' AND status='0'";
				$counter = $conn->query($sqlGetCount);
				$count = $counter->num_rows;
				$sqlGetCountNotify = "SELECT * FROM notifycations WHERE reciever_id='" . $row["id"] . "' AND type in (1,2,3) AND pending='0'";
				$notifyCounter = $conn->query($sqlGetCountNotify);
                $notCount = $notifyCounter->num_rows;
				$this->data = array(
                    "action"  => "login",
                    "account" => array(
                        "username"  => $row["username"],
                        "name"  => $row["name"],
                        "sur_name"  => $row["s_name"],
                        "rank"  => $row["rank"],
                        "user_id"   => $row["id"],
                        "noti_msg"   => $row["msg_not"],
                        "noti_req"   => $row["req_not"],
                        "active"   => $row["active"],
                        "sklad"    => $row["sklad"],
						"neotrazeni" => $count,
						"izvestiq" => $notCount
                    )
                );
                $result->free();
            }else{
                $this->data = array("action" => "register");
            }
            return json_encode($this->data,JSON_UNESCAPED_UNICODE);
        }

        function register($f_name,$s_name,$dev_id,$user_name){
            require("config.php");
            if(empty($f_name) || ctype_space($f_name)){
                $this->data = array("result" => "error");
            }else{
                if(empty($s_name) || ctype_space($s_name)){
                    $this->data = array("result" => "error");
                }else{
                    if(empty($user_name) || ctype_space($user_name)){
                        $this->data = array("result" => "error");
                    }else{
                        $sql = "INSERT INTO account (username,name,s_name,device_id) VALUES ('" . $user_name . "','" . $f_name . "','" . $s_name . "','" . $dev_id . "')";
                        if ($conn->query($sql) === TRUE) {
                            $this->data = array("result" => "ok");
                        } else {
                            $this->data = array("result" => $conn->error);
                        }
                    }
                }
            }
            
            return json_encode($this->data,JSON_UNESCAPED_UNICODE);
        }
        function push_notify($acc_id){
            require("config.php");
            // get notifications count -> send them  -> set count to 0 so app wont spamm
            $sql = "SELECT * FROM notifycations WHERE reciever_id='" . $acc_id . "' AND send_to_app='0' AND reciever_id<>'-1'";
            $result = $conn->query($sql);
            $count = $result->num_rows;
            if($count > 0){
                $this->data = array("count" => $count);
            }
            // update as seen in app
            foreach($result as $row){
                $notify_id = $row["id"];
                $sqlUpdate = "UPDATE notifycations SET send_to_app='1' WHERE id='" . $notify_id . "'";
                $conn->query($sqlUpdate) === TRUE;
            }
            //$this->data = array("count" => "10");
            return json_encode($this->data,JSON_UNESCAPED_UNICODE);
        }

        function updateMe(){
            require("config.php");
            $sqlGetVer = "SELECT * FROM updates ORDER BY date DESC LIMIT 1";
            $version = $conn->query($sqlGetVer);
            $row = $version->fetch_assoc();
            $this->tempData = array(
				"last_version" => $row["version"],
				"info" => $row["info"]
			);
			array_push($this->data,$this->tempData);
            return json_encode($this->data,JSON_UNESCAPED_UNICODE);
        }

        function inAppNotify($acc_id,$sklad_id){
            require("config.php");
            $sqlGetNotify = "SELECT * FROM notifycations WHERE reciever_id='" . $acc_id . "' OR reciever_id='0' AND status='0' ORDER BY type ASC";
            $result = $conn->query($sqlGetNotify);
            if($result->num_rows > 0){
                
                foreach($result as $row){
					if($row["pending"] == 0){
                    if($row["reciever_id"] == 0){
                        // system notifycation
                        $rowD = $row["status"];
                        switch($rowD){
                            case 0:
                                // permanent system notifycation
                                $this->tempData = array(
                                    "notification_id" => $row["id"],
                                    "notification_type" => "SYSTEM",
                                    "notification_text" => "(!) SYSTEM " . $row["text"]
                                );
                                array_push($this->data,$this->tempData);
                            break;
                            case 1:
                                // 1 week system notifycation
                                array_push($this->data,"1 week notifycation");
                            break;
                            case 2:
                                // 1 day system notifycation
                                array_push($this->data,"1 day notifycation");
                            break;
                        }
                    }else{
                        // if status is pending
                        
                            // notifycation from other user
                            
                            $sqlFindUserNames = "SELECT * FROM account WHERE id='" . $row["sender_id"] . "'"; // get usernames of sender
                            $userData = $conn->query($sqlFindUserNames);
                            
                            $USER_DATA = $userData->fetch_assoc();
                            
                            switch($row["type"]){
                                case 1:
                                    // second shift date request from user
                                    $sqlGetDateById = "SELECT * FROM dates WHERE id='" . $row["text"] . "' AND sklad='" . $sklad_id . "'"; // get real date from given id
                                    $dateInfo = $conn->query($sqlGetDateById);
                                    if($dateInfo->num_rows > 0){
                                        $DATE_INFO = $dateInfo->fetch_assoc();
                                        $DATE_T = $DATE_INFO["date"]; // to convert in user friendly type
                                    }
                                    $this->tempData = array(
                                        "notification_id" => $row["id"],
                                        "notification_type" => "SHIFT_REQUEST",
                                        "notification_text" => "Заявка за размяна на втора смяна на дата - " . $DATE_T . " от " . $USER_DATA["name"] . " " . $USER_DATA["s_name"]
                                    );
                                    array_push($this->data,$this->tempData);        
                                break;
                                case 2:
                                    // sunday date request
                                    $sqlGetDateById = "SELECT * FROM dates WHERE id='" . $row["text"] . "'"; // get real date from given id
                                    $dateInfo = $conn->query($sqlGetDateById);
                                    if($dateInfo->num_rows > 0){
                                        $DATE_INFO = $dateInfo->fetch_assoc();
                                        $DATE_T = $DATE_INFO["date"]; // to convert in user friendly type
                                    }
                                    $this->tempData = array(
                                        "notification_id" => $row["id"],
                                        "notification_type" => "SUNDAY_REQUEST",
                                        "notification_text" => "Заявка за размяна на неделя на дата - " . $DATE_T . " от " . $USER_DATA["name"] . " " . $USER_DATA["s_name"]
                                    );
                                    array_push($this->data,$this->tempData);
                                break; 
                                case 3:
                                    $sqlGetDateById = "SELECT * FROM dates WHERE id='" . $row["text"] . "'"; // get real date from given id
                                    $dateInfo = $conn->query($sqlGetDateById);
                                    if($dateInfo->num_rows > 0){
                                        $DATE_INFO = $dateInfo->fetch_assoc();
                                        $DATE_T = $DATE_INFO["date"]; // to convert in user friendly type
                                    }
                                        $this->tempData = array(
                                            "notification_id" => $row["id"],
                                            "notification_type" => "REST_REQUEST",
                                            "notification_text" => "Заявка за размяна на почивка на дата - " . $DATE_T . " от " . $USER_DATA["name"] . " " . $USER_DATA["s_name"]
                                        );
                                        array_push($this->data,$this->tempData); 
                                break;
								case 4:
									// get message
										$this->tempData = array(
                                            "notification_id" => $row["id"],
                                            "notification_type" => "REST_REQUEST",
                                            "notification_text" => $row["text"]
                                        );
                                        array_push($this->data,$this->tempData);
								break;
                            }
						}
					}
                }
            }
            return json_encode($this->data,JSON_UNESCAPED_UNICODE);
        }
        function addNewDocument($accId,$sklad,$data){
            require("config.php");
            $sqlCheck = "SELECT * FROM documents WHERE doc_number='" . $data . "' AND sklad='" . $sklad . "'";
            $result = $conn->query($sqlCheck);
            $hasBeenDone = false;
            $registeredUserId;
            $registeredUserNames;
            if($result->num_rows > 0){
                foreach($result as $row){
                    if($row["doc_number"] == $data and $row["sklad"] == $sklad){
                        $hasBeenDone = true;
                        $registeredUserId = $row["owner_id"];
                    }
                }

            }
            if($hasBeenDone){
                $sqlGetUser = "SELECT * FROM account WHERE id=" . $registeredUserId;
                $results = $conn->query($sqlGetUser);
                    if($results->num_rows > 0){
                        $row = $results->fetch_assoc();
                        $registeredUserNames = $row["name"] . " " . $row["s_name"];
                    }
                if($registeredUserId == $accId){
                    $this->tempData = array(
                        "RESPONSE" => "Тази бележка е отбелязана от Вас !!!"
                    );
                }else{
                    $this->tempData = array(
                        "RESPONSE" => "Тази бележка е отбелязана от потребител - " . $registeredUserNames
                    );
                } 
                
            }else{
                $sql = "INSERT INTO documents (doc_number,owner_id,sklad,date) VALUES('" . $data . "','" . $accId . "','" . $sklad . "','" . date("Y-m-d") . "')";
                if ($conn->query($sql) === TRUE) {
                    $this->tempData = array(
                        "RESPONSE" => "Бележката е добавена !!!"
                    );
                } else {
                    $this->tempData = array("result" => $conn->error);
                }
            }
            array_push($this->data,$this->tempData);
            return json_encode($this->data,JSON_UNESCAPED_UNICODE);
        }


        function deleteDocument($accId,$sklad,$data){
            require("config.php");
            $sqlCheck = "SELECT * FROM documents WHERE doc_number='" . $data . "' AND sklad='" . $sklad . "' AND owner_id=" . $accId;
            $result = $conn->query($sqlCheck);
            if($result->num_rows > 0){
                // delete the document
                $sqlDelete = "DELETE FROM documents WHERE doc_number='" . $data . "' AND sklad='" . $sklad . "' AND owner_id=" . $accId;
                if ($conn->query($sqlDelete) === TRUE) {
                    $this->tempData = array(
                        "RESPONSE" => "Бележката е изтрита успешно !!!"
                    );
                } else {
                    $this->tempData = array("result" => $conn->error);
                }
            }else{
                $this->tempData = array(
                    "RESPONSE" => "Тази бележка не е ваша и не можете да я изтриете !!!"
                );
            }
            array_push($this->data,$this->tempData);
            return json_encode($this->data,JSON_UNESCAPED_UNICODE);
        }


        function findDocument($accId,$sklad,$data){
            require("config.php");
            $sqlPopulateAccounts = "SELECT * FROM account";
            $accResult = $conn->query($sqlPopulateAccounts);
            if($accResult){
                foreach($accResult as $accRow){
                    $tempAcc = array(
                        "USER_ID" => $accRow["id"],
                        "USER_NAMES" => $accRow["name"] . " " . $accRow["s_name"]
                    );
                    array_push($this->accounts,$tempAcc);
                }
            }
            $sqlFindDocument = "SELECT * FROM documents WHERE doc_number LIKE '%" . $data . "%' LIMIT 50";
            $resultDocuments = $conn->query($sqlFindDocument);
           
            if($resultDocuments->num_rows > 0){
                foreach($resultDocuments as $rowDocument){
                    foreach($this->accounts as $accounts){
                        if($accounts["USER_ID"] == $rowDocument["owner_id"]){
                            $this->realName = strtoupper($accounts["USER_NAMES"]);
                        }
                    }
                    $this->tempData = array(
                        "DOC_NUMBER" => $rowDocument["doc_number"],
                        "OWNER" => $this->realName,
                        "SKLAD" => $this->skladNames[$rowDocument["sklad"]],
                        "STATUS" => $this->docStatus[$rowDocument["status"]],
                        "DATE" => $rowDocument["date"]
                    );
                    array_push($this->data,$this->tempData);
                }
            }
            return json_encode($this->data,JSON_UNESCAPED_UNICODE);
        }


        function listAllDocument($accId,$sklad,$date,$year){
            require("config.php");
            $currentMonth = date('m', time());
            $countNonEnteredDocs =0;
            $countEnteredDocs =0;
            $sqlCountDocsNo = "SELECT * FROM documents WHERE owner_id='" . $accId . "' AND status='0' AND MONTH(date) = '" . $date . "' AND YEAR(date) ='" . $year . "'";
            $resulnDocsNo = $conn->query($sqlCountDocsNo);
            if($resulnDocsNo->num_rows > 0){
                $countNonEnteredDocs = $resulnDocsNo->num_rows;
            }

            $sqlCountDocsYes = "SELECT * FROM documents WHERE owner_id='" . $accId . "' AND status='1' AND MONTH(date) = '" . $date . "' AND YEAR(date) ='" . $year . "'";
            $resulnDocsYes = $conn->query($sqlCountDocsYes);
            if($resulnDocsYes->num_rows > 0){
                $countEnteredDocs = $resulnDocsYes->num_rows;
            }
            $this->tempData = array(
                "NON_ENTERED" => $countNonEnteredDocs,
                "ENTERED" => $countEnteredDocs,
                "TOTAL" => $countEnteredDocs + $countNonEnteredDocs
            );
            array_push($this->data,$this->tempData);
            $sqlGetDocuments = "SELECT * FROM documents WHERE owner_id='" . $accId . "' AND MONTH(date) = '" . $date . "' AND YEAR(date) ='" . $year . "' ORDER BY status ASC";
            $docsResultList = $conn->query($sqlGetDocuments);
            if($docsResultList->num_rows > 0){
                foreach($docsResultList as $myData){
                    $this->tempData = array(
                        "DOC_NUMBER" => $myData["doc_number"],
                        "DOC_DATE" => $myData["date"],
                        "DOC_STATUS" => $this->docStatus[$myData["status"]]
                    );
                    array_push($this->data,$this->tempData);
                }
            }
            
            return json_encode($this->data,JSON_UNESCAPED_UNICODE);
        }


        function enteringMode($accId,$sklad){
            require("config.php");
            // to do @@@@@@@@@@@@@@@@@
            $sqlGetDocuments = "SELECT * FROM documents WHERE owner_id='" . $accId . "' AND sklad='" . $sklad . "' AND MONTH(date) = MONTH(NOW()) AND status='0' ORDER BY doc_number ASC";
            $docsResultList = $conn->query($sqlGetDocuments);
            if($docsResultList->num_rows > 0){
                foreach($docsResultList as $myData){
                    $this->tempData = array(
                        "DOC_NUM" => $myData["doc_number"],
                        "DOC_ID" => $myData["id"]
                    );
                    array_push($this->data,$this->tempData);
                }
            }
            array_push($this->data,$this->tempData);
            return json_encode($this->data,JSON_UNESCAPED_UNICODE);
        }
		function checkDocument($docId){
			require("config.php");
			$sqlUpdateState = "UPDATE documents SET status='1' WHERE id='" . $docId . "'";
			if ($conn->query($sqlUpdateState) === TRUE) {
                $this->tempData = array(
					"RESPONSE" => "DONE"
				);
            } else {
                $this->tempData = array(
					"RESPONSE" => "ERROR"
				);
            }
            array_push($this->data,$this->tempData);
			return json_encode($this->data,JSON_UNESCAPED_UNICODE);
		}
		function getCalendar($year,$month,$sklad){
			require("config.php");
			$sqlGetDates = "SELECT * FROM dates WHERE MONTH(date) = '" . $month . "' AND YEAR(date) ='" . $year . "' AND sklad='" . $sklad . "' ORDER BY date ASC";
			$datesResult = $conn->query($sqlGetDates);
			if($datesResult->num_rows > 0){
				foreach($datesResult as $result){
					$dayNumber = explode('-', $result["date"]);
					$dayNumber = ltrim($dayNumber[2], '0');
					$this->tempData = array(
						"USER" => $result["ovner_id"],
						"TYPE" => $result["type"],
						"NUMBER" => $dayNumber
					);
					array_push($this->data,$this->tempData);
				}	
			}
			return json_encode($this->data,JSON_UNESCAPED_UNICODE);
		}
		
		function get_request_list($reciever,$sklad){
			require("config.php");
			$sqlGetData = "SELECT * FROM notifycations WHERE reciever_id='" . $reciever . "' AND pending='0' ";
			$datesResult = $conn->query($sqlGetData);
			$user_names = "";
			$date_text = "";
			$date_id = "";
			$senderId = "";
			$notify_id = "";
			if($datesResult->num_rows > 0){
				foreach($datesResult as $result){
					if($result["type"] <= 3){
					$senderId = $result["sender_id"];
					$notify_id = $result["id"];
					$sqlFindUser = "SELECT name,s_name FROM account WHERE id='" . $result["sender_id"] . "'";
					$findUser = $conn->query($sqlFindUser);
					if($findUser->num_rows > 0){
						foreach($findUser as $user){
							$user_names = $user["name"] . " " . $user["s_name"];
						}
					}
					if(is_numeric($result["text"])){
						$sqlFindDate = "SELECT * FROM dates WHERE id='" . $result["text"] . "'";
						$dateResult = $conn->query($sqlFindDate);
						if($dateResult->num_rows > 0){
							foreach($dateResult as $row){
								$date_text = $row["date"];
								$date_id = $row["id"];
							}
						}
						$this->tempData = array(
							"NOT_TYPE" => $result["type"],
							"NOT_DATE" => $date_text,
							"NOT_SENDER" => $user_names,
							"NOT_DATE_ID" => $date_id,
							"NOT_SENDER_ID" => $senderId,
							"NOT_ID" => $notify_id
						);
					
					}
					array_push($this->data,$this->tempData);
					}
				}	
			}
			return json_encode($this->data,JSON_UNESCAPED_UNICODE);
		}
		function acceptRequest($dateId,$senderId,$not_id,$reciever,$names,$msg,$dateString){
			require("config.php");
			$sqlAccept = "UPDATE notifycations SET pending='1' WHERE id='" . $not_id . "'";
			$msgA = "Потребител " . $names . " прие вашата заявка за " . $msg ." за дата - " . $dateString;
			$sqlSendNotifyA = "INSERT INTO notifycations (sender_id,reciever_id,type,status,text,send_to_app,pending) 
				VALUES('" . $reciever . "','" . $senderId . "','4','0','" . $msgA . "','0','2')";
			$sqlUpdateDates = "UPDATE dates SET ovner_id='" . $senderId . "' WHERE id='" . $dateId . "'";
			// execute queries and return info
			if($conn->query($sqlAccept) === TRUE){
				if($conn->query($sqlSendNotifyA) === TRUE){
					if($conn->query($sqlUpdateDates) === TRUE){
						$this->tempData = array(
							"RESULT" => 1
						);
						array_push($this->data,$this->tempData);
					}else{
						$this->tempData = array(
							"RESULT" => 3
						);
						array_push($this->data,$this->tempData);
					}
				}else{
					$this->tempData = array(
						"RESULT" => 3
					);
					array_push($this->data,$this->tempData);
				}
			}else{
				$this->tempData = array(
					"RESULT" => 3
				);
				array_push($this->data,$this->tempData);
			}
			return json_encode($this->data,JSON_UNESCAPED_UNICODE);
		}
		function declineRequest($dateId,$senderId,$not_id,$reciever,$names,$msg,$dateString){
			require("config.php");
			$sqlDecline = "UPDATE notifycations SET pending='2' WHERE id='" . $not_id . "'";
			$msgA = "Потребител " . $names . " отказа вашата заявка за " . $msg ." за дата - " . $dateString;
			$sqlSendNotify = "INSERT INTO notifycations (sender_id,reciever_id,type,status,text,send_to_app,pending) 
				VALUES('" . $reciever . "','" . $senderId . "','4','0','" . $msgA . "','0','3')";
				
			if($conn->query($sqlDecline) === TRUE){
				if($conn->query($sqlSendNotify) === TRUE){
					$this->tempData = array(
						"RESULT" => 0
					);
					array_push($this->data,$this->tempData);
				}
			}else{
				$this->tempData = array(
					"RESULT" => 3
				);
				array_push($this->data,$this->tempData);
			}
			// izvestieto e pending=1
			// izprati izvestie s otkaz
			return json_encode($this->data,JSON_UNESCAPED_UNICODE);
		}
		
		function get_doc_count($month,$acc_id,$sklad){
			require("config.php");
			$sqlGetDoc = "SELECT * FROM documents WHERE owner_id='" . $acc_id . "' AND status='0' AND MONTH(date) = '" . $month . "'";
			$sqlGetAll = "SELECT * FROM documents WHERE owner_id='" . $acc_id . "' AND MONTH(date) = '" . $month . "'";
			$countResult = $conn->query($sqlGetAll);
			$coutUn = $conn->query($sqlGetDoc);
			$total = 0;
			$unChecked = 0;
			$hours_count = 0;
			$days_count = 0;
			
			$getDays = "SELECT * FROM extra WHERE owner='" . $acc_id . "' AND type='1' AND count >='1'";
			$res = $conn->query($getDays);
			if($res->num_rows > 0){
				$days_count = $res->num_rows;
			}
			
			$getHours =  "SELECT * FROM extra WHERE owner='" . $acc_id . "' AND type='2' AND count >='1'";
			$resH = $conn->query($getHours);
			if($resH->num_rows > 0){
				foreach($resH as $hour){
					$hours_count = $hours_count + $hour["count"];
				}
			}
	
			if($countResult->num_rows > 0){
				$total = $countResult->num_rows;
			}
			
			if($coutUn->num_rows > 0){
				$unChecked = $coutUn->num_rows;
			}
			
			$this->tempData = array(
				"COUNT_TOTAL" => $total,
				"COUNT_UNCHECKED" => $unChecked,
				"COUNT_DAYS" => $days_count,
				"COUNT_HOURS" => $hours_count
			);
			array_push($this->data,$this->tempData);
			return json_encode($this->data,JSON_UNESCAPED_UNICODE);
		}
		
		
		function check_date($year,$month,$day,$acc,$sklad){
			require("config.php");
			$userNames = "";
			$sqlFindDates = "SELECT * FROM dates WHERE YEAR(date) = '" . $year . "' AND MONTH(date) = '" . $month . "' AND DAY(date) = '" . $day . "' AND sklad='" . $sklad . "'";
			$dateResult = $conn->query($sqlFindDates);
			if($dateResult->num_rows > 0){
				foreach($dateResult as $date){
					$sqlGetNames = "SELECT * FROM account WHERE id='" . $date["ovner_id"] . "'";
					$resultNames = $conn->query($sqlGetNames);
					if($resultNames->num_rows > 0){
						foreach($resultNames as $names){
							$userNames = $names["name"] . " " . $names["s_name"];
						}
					}
					$this->tempData = array(
						"DATE_ID" => $date["id"],
						"DATE" => $date["date"],
						"DATE_TYPE" => $date["type"],
						"DATE_OWNER_NAMES" => $userNames,
						"DATE_OWNER_ID" => $date["ovner_id"]
					);
					array_push($this->data,$this->tempData);
				}
			}
			
			return json_encode($this->data,JSON_UNESCAPED_UNICODE);
		}
					
		function request_date($req_type,$date_type,$my_acc_id,$sklad,$year,$month,$day,$old_date_id,$old_date_text,$old_date_owner_id,$my_names){
			require("config.php");
			
			
			// req_type = 1 svoboden den
			// date_type -> 1 vtora 2 nedelq 3 pochivka
			
			if($req_type == 1){
				// za prazni dati
				$finalDate = $year . "-" . $month . "-" . $day;
				$sqlSetNewDate = "INSERT INTO dates (ovner_id,sklad,type,date) VALUES('" . $my_acc_id . "','" . $sklad . "','" . $date_type . "','" . $finalDate ."')";
				try{
					if($conn->query($sqlSetNewDate) === TRUE){
						$this->tempData = array(
							"REQ_STATE" => 1,
							"REQ_DATE" => $finalDate 
						);
						array_push($this->data,$this->tempData);
					}else{
						$this->tempData = array(
							"REQ_STATE" => 0,
							"REQ_DATE" => "SQL ERROR"
						);
						array_push($this->data,$this->tempData);
					}
				}catch(MySQLException $e){
					$this->tempData = array(
						"REQ_STATE" => 0,
						"REQ_DATE" => $e
					);
					array_push($this->data,$this->tempData);		
				}
				
			}else if($req_type == 2){
				$finalDate = $year . "-" . $month . "-" . $day;
				$sqlPrepare = "INSERT INTO notifycations (sender_id,reciever_id,type,status,text,send_to_app,pending) VALUES ('". $my_acc_id ."','" . $old_date_owner_id . "','" . $date_type . "','0','" . $old_date_id . "','0','0')";
				try{
					if($conn->query($sqlPrepare) === TRUE){
						$this->tempData = array(
							"REQ_STATE" => 1,
							"REQ_DATE" => $finalDate
						);
						array_push($this->data,$this->tempData);
					}else{
						$this->tempData = array(
							"REQ_STATE" => 0,
							"REQ_DATE" => "SQL ERROR"
						);
						array_push($this->data,$this->tempData);
					}
				}catch(MySQLException $e){
					$this->tempData = array(
						"REQ_STATE" => 0,
						"REQ_DATE" => $e
					);
					array_push($this->data,$this->tempData);
				}
			}
			return json_encode($this->data,JSON_UNESCAPED_UNICODE);
		}
		function update_token($acc_id,$token){
			require("config.php");
			$sql = "UPDATE account SET token='" . $token . "' WHERE id='" . $acc_id . "'";
			try{
				if($conn->query($sql) === TRUE){
					$this->tempData = array(
						"REQ_STATE" => 1,
						"RESPONSE" => "OK"
					);
					array_push($this->data,$this->tempData);
				}else{
					$this->tempData = array(
						"REQ_STATE" => 0,
						"RESPONSE" => "SQL ERROR"
					);
					array_push($this->data,$this->tempData);
				}
			}catch(MySQLException $e){
				$this->tempData = array(
					"REQ_STATE" => 0,
					"RESPONSE" => $e
				);
				array_push($this->data,$this->tempData);
			}
			return json_encode($this->data,JSON_UNESCAPED_UNICODE);
		}
		
		function get_extra_dates($acc_id){
			require("config.php");
			$sqlDisplayDates = "SELECT * FROM extra WHERE owner='". $acc_id ."' AND count >= '1'";
			$result = $conn->query($sqlDisplayDates);
			if($result->num_rows > 0){
				foreach($result as $row){
					$this->tempData = array(
						"DATE_ID" => $row["id"],
						"DATE_TYPE" => $row["type"],
						"DATE_OWNER" => $row["owner"],
						"DATE" => $row["date"],
						"DATE_VOLUME" => $row["count"]
					);
					array_push($this->data,$this->tempData);
				}
			}
			
			return json_encode($this->data,JSON_UNESCAPED_UNICODE);
		}
		
		function add_extra_date($acc_id,$type,$count,$date){
			require("config.php");
			if($type == 1){
				$sqlInsert = "INSERT INTO extra (owner,type,count,date) VALUES('" . $acc_id . "','" . $type . "','1','" . $date . "')";
				try{
					if($conn->query($sqlInsert) === TRUE){
						$this->tempData = array(
							"REQ_STATE" => 1,
							"RESPONSE" => "OK"
						);
						array_push($this->data,$this->tempData);
					}
				}catch(MySQLException $e){
					$this->tempData = array(
						"REQ_STATE" => 0,
						"RESPONSE" => $e
					);
					array_push($this->data,$this->tempData);
				}				
			}else if($type == 2){
				$sqlInsert = "INSERT INTO extra (owner,type,count,date) VALUES('" . $acc_id . "','" . $type . "','" . $count . "','" . $date . "')";
				try{
					if($conn->query($sqlInsert) === TRUE){
						$this->tempData = array(
							"REQ_STATE" => 1,
							"RESPONSE" => "OK"
						);
						array_push($this->data,$this->tempData);
					}
				}catch(MySQLException $e){
					$this->tempData = array(
						"REQ_STATE" => 0,
						"RESPONSE" => $e
					);
					array_push($this->data,$this->tempData);
				}
			}
			
			return json_encode($this->data,JSON_UNESCAPED_UNICODE);
		}
	}
?>