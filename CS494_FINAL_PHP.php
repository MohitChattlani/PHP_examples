<?php    
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 'On');   
    ini_set('display_startup_errors', 1);
    error_reporting(-1);

    $act = $_REQUEST['act'];
    $jarr;
    
    $mysqli = new mysqli('oniddb.cws.oregonstate.edu','mcqueejo-db','UfrFyrfejhu1toXP','mcqueejo-db');

    if(mysqli_connect_error())
    {
        die('Connect Error(' . mysqli_connect_errno() . ') '
            . mysqli_connect_error());
    }
    
    switch($act)
    {
        case 'check_login':
            $result = $mysqli->query('SELECT accountID FROM Accounts WHERE accountName = \''.
                    $_REQUEST['u_name'].'\' AND accountPW = \''.$_REQUEST['u_pw'].'\'');
            break;
        case 'check_name':
            $result = $mysqli->query('SELECT accountID FROM Accounts WHERE accountName = \''.
                    $_REQUEST['u_name'].'\'');            
            break;
        case 'make_account':
            $result = $mysqli->query('INSERT INTO Accounts (accountName, accountPW)'
                    .'VALUES (\''.$_REQUEST['u_name'].'\', \''.$_REQUEST['u_pw'].'\')');
            break;
        case 'save_canvas':
            //$json_str = json_decode($_REQUEST['canvas_path']);//json_encode($_REQUEST['canvas_path']);
            //if(!$mysqli->query('SELECT pathdata FROM Accounts WHERE accountName = '.$_REQUEST['name']))
            //{
            $result = $mysqli->query('UPDATE Accounts SET pathData = \''.$_REQUEST['canvas_path'].
                    '\' WHERE accountName = \''.$_REQUEST['account_name'].'\'');
            //}
            //else
            //{
                //update
            //}
            //$result = $mysqli->query();
            break;
        case 'load_canvas':
            $result = $mysqli->query('SELECT pathdata FROM Accounts WHERE accountName = \''.
                    $_REQUEST['account_name'].'\'');
            break;
        
    }
    
    if($act === 'load_canvas')
    {
        $ret_arr = process_result($result, $act);
        echo $ret_arr[0];
        mysqli_close($mysqli);
    }
    else
    {
        echo json_encode(process_result($result, $act));
        mysqli_close($mysqli);        
    }

    
    function process_result($result, $act)
    {
        switch($act)
        {
            case 'check_login':           
                if(!mysqli_fetch_array($result))
                {
                    $jarr = array( 'status' => 'login_incorrect_info');
                }
                else
                {
                    $jarr = array( 'status' => 'login_ok');
                }
                return $jarr;
            case 'check_name':
                if(mysqli_fetch_array($result))
                {
                    $jarr = array( 'status' => 'name_taken');
                }
                else
                {
                    $jarr = array( 'status' => 'name_ok');
                }
                return $jarr;
            case 'make_account':
                $result = $mysqli->query('SELECT accountID FROM Accounts WHERE accountName = \''.
                    $_REQUEST['u_name'].'\' AND accountPW = \''.$_REQUEST['u_pw'].'\'');
                if(!mysqli_fetch_array($result))
                {
                    $jarr = array( 'status' => 'insert_error');
                }
                else
                {
                    $jarr = array( 'status' => 'insert_ok');
                }
                return $jarr;
            case 'save_canvas':
                $jarr = array('status' => 'testing');
                return $jarr;
            case 'load_canvas':
                $row = mysqli_fetch_row($result);
                return $row;
//                while($row = mysqli_fetch_array($result))
//                {
//                    $jarr[] = $row;
//                }
//                return $jarr;
        }
    }
?>