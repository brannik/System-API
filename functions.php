<?php
    class functions{
        
        public $data = array();
        public $tempData = array();
        public $accounts = array();
        public $realName = "NONE";
        public $skladNames = array("HOOCK","Първи","Втори","Трети","Четвърти","Победа");
        public $docStatus = array("НЕ Отразена","Отразена");
        function logIn($id){
            require("config.php");
            $sql = "SELECT * FROM account WHERE device_id='" . $id . "'";
            $result = $conn->query($sql);
            if($result->num_rows > 0){
                $row = $result->fetch_assoc();
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
            $sqlGetVer = "SELECT * FROM updates ORDER BY version ASC LIMIT 1";
            $version = $conn->query($sqlGetVer);
            $row = $version->fetch_assoc();
            $this->data = array("last_version" => $row["version"]);
            return json_encode($this->data,JSON_UNESCAPED_UNICODE);
        }

        function inAppNotify($acc_id,$sklad_id){
            require("config.php");
            $sqlGetNotify = "SELECT * FROM notifycations WHERE reciever_id='" . $acc_id . "' OR reciever_id='0' ORDER BY type ASC";
            $result = $conn->query($sqlGetNotify);
            if($result->num_rows > 0){
                
                foreach($result as $row){
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
                        
                        if($row["status"] == 0){
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
                                    // notification user accepted/declined your date request
                                    if($row["status"] == 0){
                                        
                                        $this->tempData = array(
                                            "notification_id" => $row["id"],
                                            "notification_type" => "RESULT_REQUEST",
                                            "notification_text" => $row["text"]
                                        );
                                        array_push($this->data,$this->tempData); 
                                    }
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
            $sqlFindDocument = "SELECT * FROM documents WHERE doc_number LIKE '%" . $data . "%'";
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


        function listAllDocument($accId,$sklad,$data){
            require("config.php");
            $currentMonth = date('m', time());
            $countNonEnteredDocs =0;
            $countEnteredDocs =0;
            $sqlCountDocsNo = "SELECT * FROM documents WHERE owner_id='" . $accId . "' AND status='0' AND MONTH(date) = MONTH(NOW())";
            $resulnDocsNo = $conn->query($sqlCountDocsNo);
            if($resulnDocsNo->num_rows > 0){
                $countNonEnteredDocs = $resulnDocsNo->num_rows;
            }

            $sqlCountDocsYes = "SELECT * FROM documents WHERE owner_id='" . $accId . "' AND status='1' AND MONTH(date) = MONTH(NOW())";
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
            $sqlGetDocuments = "SELECT * FROM documents WHERE owner_id='" . $accId . "' AND MONTH(date) = MONTH(NOW()) ORDER BY status ASC";
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
            //  ssdfsdfsdf
		}
    }
?>