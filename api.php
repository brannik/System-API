<?php
    require("config.php");
    require("functions.php");

    define("LOGIN","login");
    define("REGISTER","register");
    define("NOTIFYCATION","notify");
    define("UPDATE_REQUEST","update");
    define("IN_APP_NOTIFY","in_app_notify");

    define("NEW_DOCUMENT","add_new_document"); 
    define("DELETE_DOCUMENT","delete_document"); 
    define("FIND_DOCUMENT","find_document"); 
    define("LIST_ALL_DOCUMENT","list_document"); 
    define("ENTERING_MODE","entering_mode"); 
	define("ENTERING_MODE_DOCUMENT","entering_mode_document");
	define("GET_CALENDAR","get_calendar");
	define("GET_REQUESTS","get_requests");
	define("ACCEPT_REQUEST","accept_request"); 
	define("DECLINE_REQUEST","decline_request"); 
	define("GET_DOC_COUNT","get_doc_count");
	define("VALIDATE","get_min_max");
	define("CHECK_DATE","check_date");
	define("REQUEST_DATE","request_date");
    define("TEST","test");

    $function = new functions();

    if(isset($_GET["request"])){
		
        switch($_GET["request"]){
            case LOGIN: // done
                echo $function->login($_GET["dev_id"]);
            break;
            case REGISTER: 
                echo $function->register($_GET["f_name"],$_GET["s_name"],$_GET["dev_id"],$_GET["username"]);
            break;
            case NOTIFYCATION: 
                echo $function->push_notify($_GET["acc_id"]);
            break;
            case UPDATE_REQUEST: 
                echo $function->updateMe();
            break;
            case TEST: 
                echo "Test function is working";
            break;
            case IN_APP_NOTIFY: 
                echo $function->inAppNotify($_GET["acc_id"],$_GET["sklad_id"]);
            break;
            case NEW_DOCUMENT: 
                echo $function->addNewDocument($_GET["acc_id"],$_GET["sklad"],$_GET["data"]);
                break;
            case DELETE_DOCUMENT: 
                echo $function->deleteDocument($_GET["acc_id"],$_GET["sklad"],$_GET["data"]);
                break;
            case FIND_DOCUMENT:
                echo $function->findDocument($_GET["acc_id"],$_GET["sklad"],$_GET["data"]);
                break;
            case LIST_ALL_DOCUMENT:
                echo $function->listAllDocument($_GET["acc_id"],$_GET["sklad"],$_GET["data"],$_GET["year"]);
                break;
            case ENTERING_MODE:
                echo $function->enteringMode($_GET["acc_id"],$_GET["sklad"]);
                break;
			case ENTERING_MODE_DOCUMENT:
				echo $function->checkDocument($_GET["doc_id"]);
				break;
			case GET_CALENDAR:
				echo $function->getCalendar($_GET["year"],$_GET["month"],$_GET["sklad"]);
				break;
			case GET_REQUESTS:
				echo $function->get_request_list($_GET["acc_id"],$_GET["sklad"]);
				break;
			case ACCEPT_REQUEST:
				echo $function->acceptRequest($_GET["date_id"],$_GET["sender"],$_GET["notify_id"],$_GET["my_acc"],$_GET["names"],$_GET["message"],$_GET["dateStr"]);
				break;
			case DECLINE_REQUEST:
				echo $function->declineRequest($_GET["date_id"],$_GET["sender"],$_GET["notify_id"],$_GET["my_acc"],$_GET["names"],$_GET["message"],$_GET["dateStr"]);
				break;
			case GET_DOC_COUNT:
				echo $function->get_doc_count($_GET["month"],$_GET["acc_id"],$_GET["sklad"]);
				break;
			case VALIDATE:
				echo $function->validate();
				break;
			case CHECK_DATE:
				echo $function->check_date($_GET["year"],$_GET["month"],$_GET["day"],$_GET["acc_id"],$_GET["sklad"]);
				break;
			case REQUEST_DATE:
				echo $function->request_date($_GET["req_type"],$_GET["date_type"],$_GET["my_acc"],$_GET["sklad"],$_GET["year"],$_GET["month"],$_GET["day"],$_GET["old_date_id"],$_GET["old_date_text"],$_GET["old_date_owner"],$_GET["my_names"]);
				break;
            default:
                echo json_encode("NO_REQUEST");
        }
    }
?>