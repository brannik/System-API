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
    define("TEST","test");

    $function = new functions();

    if(isset($_GET["request"])){
        switch($_GET["request"]){
            case LOGIN: // done
                echo $function->login($_GET["dev_id"]);
            break;
            case REGISTER: // done
                echo $function->register($_GET["f_name"],$_GET["s_name"],$_GET["dev_id"],$_GET["username"]);
            break;
            case NOTIFYCATION: // done
                echo $function->push_notify($_GET["acc_id"]);
            break;
            case UPDATE_REQUEST: // done
                echo $function->updateMe();
            break;
            case TEST: // done
                echo "Test function is working";
            break;
            case IN_APP_NOTIFY: // done
                echo $function->inAppNotify($_GET["acc_id"],$_GET["sklad_id"]);
            break;
            case NEW_DOCUMENT: // done
                echo $function->addNewDocument($_GET["acc_id"],$_GET["sklad"],$_GET["data"]);
                break;
            case DELETE_DOCUMENT: // done
                echo $function->deleteDocument($_GET["acc_id"],$_GET["sklad"],$_GET["data"]);
                break;
            case FIND_DOCUMENT:
                echo $function->findDocument($_GET["acc_id"],$_GET["sklad"],$_GET["data"]);
                break;
            case LIST_ALL_DOCUMENT:
                echo $function->listAllDocument($_GET["acc_id"],$_GET["sklad"],$_GET["data"]);
                break;
            case ENTERING_MODE:
                echo $function->enteringMode($_GET["acc_id"],$_GET["sklad"]);
                break;
			case ENTERING_MODE_DOCUMENT:
				echo $function->checkDocument($_GET["doc_id"]);
				break;
            default:
                echo json_encode("NO_REQUEST");
        }
    }
?>