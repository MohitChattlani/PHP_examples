<?php    
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 'On');   
    ini_set('display_startup_errors', 1);
    error_reporting(-1);

    $act = $_REQUEST['act'];
    
    $mysqli = new mysqli('oniddb.cws.oregonstate.edu','mcqueejo-db','UfrFyrfejhu1toXP','mcqueejo-db');

    if(mysqli_connect_error())
    {
        die('Connect Error(' . mysqli_connect_errno() . ') '
            . mysqli_connect_error());
    }
       
    switch($act)
    {
        case 'key_table':
            $result = $mysqli->query("SELECT concat(table_name, '.', column_name) AS 'foreign key', 
                concat(referenced_table_name, '.', referenced_column_name) AS 'references'
            FROM
                information_schema.key_column_usage
            WHERE
                referenced_table_name IS NOT NULL");
            break;
        case 'view_table_list':
            $result = $mysqli->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'mcqueejo-db' 
                AND TABLE_NAME IN ('Characters', 'CharLocs', 'Location', 'Class', 'ClassAbl', 'Abilities', 'ClassEqp', 'Equipment')");
            break;
        case 'get_table':
            $result = $mysqli->query("SELECT * FROM ".$_REQUEST['table_name']);            
            break;
        case 'get_attr':
            $result = $mysqli->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ".$_REQUEST['table_name']." ORDER BY ordinal_position");
            break;
        case 'get_col_types':
            $result = $mysqli->query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ".$_REQUEST['table_name']." ORDER BY ordinal_position");
            break;
        case 'get_foreign_keys':
            $result = $mysqli->query("SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_COLUMN_NAME IS NOT NULL");
            break;
        case 'get_FK_vals':
            $result = $mysqli->query("SELECT C.characterID FROM Characters C ORDER BY C.characterID");
            while($row = mysqli_fetch_array($result))
            {
                $jarr['characterID'][] = $row;
            }             
            $result = $mysqli->query("SELECT L.locationID FROM Location L ORDER BY L.locationID");
            while($row = mysqli_fetch_array($result))
            {
                $jarr['locationID'][] = $row;
            } 
            $result = $mysqli->query("SELECT CL.classID FROM Class CL ORDER BY CL.classID");
            while($row = mysqli_fetch_array($result))
            {
                $jarr['classID'][] = $row;
            }             
            $result = $mysqli->query("SELECT A.abilityID FROM Abilities A ORDER BY A.abilityID");
            while($row = mysqli_fetch_array($result))
            {
                $jarr['abilityID'][] = $row;
            }    
            $result = $mysqli->query("SELECT EQ.itemID FROM Equipment EQ ORDER BY EQ.itemID");
            while($row = mysqli_fetch_array($result))
            {
                $jarr['itemID'][] = $row;
            }
            echo json_encode($jarr);
            mysqli_close($mysqli);
            return;
        case 'insert':
            // insert query string is made on client side with javascript
            $result = $mysqli->query($_REQUEST['qStr']);
            $result = $mysqli->query("SELECT * FROM ".$_REQUEST['table_name']);
            break;
        case 's_query':
            // search query string is made on client side with javascript
            if($_REQUEST['order'] !== 'none')
            {
                $temp = $_REQUEST['q_str'].' ORDER BY '.$_REQUEST['order'];
            }
            else
            {
                $temp = $_REQUEST['q_str'];
            }
            
            $result = $mysqli->query($temp);                    
            break;
        case 'del_row':
            // delete query string is made on client side with javascript
            $result = $mysqli->query($_REQUEST['del_str']);
            $result = $mysqli->query("SELECT * FROM ".$_REQUEST['table_name']);
            break;
        default:
             //".$_REQUEST['table_name'].
            break;
    }
    
    echo json_encode(process_result($result));
    mysqli_close($mysqli);
    
    function process_result($result)
    {
        switch($_REQUEST['act'])
        {
            case 'key_table':
                while($row = mysqli_fetch_array($result))
                {
                    $jarr[] = $row;
                }                  
                return $jarr;                              
            case 'view_table_list':
                while($row = mysqli_fetch_array($result))
                {
                    $jarr[] = $row[0];
                }                
                return $jarr;
            case 'get_table':               
                while($row = mysqli_fetch_array($result))
                {
                    $jarr[] = $row;
                }                  
                return $jarr;
            case 'get_attr':
                while($row = mysqli_fetch_array($result))
                {
                    $jarr[] = $row[0];
                }                  
                return $jarr;
            case 'get_col_types':
                while($row = mysqli_fetch_array($result))
                {
                    $jarr[] = $row[0];
                } 
                return $jarr;
            case 'insert':
                while($row = mysqli_fetch_array($result))
                {
                    $jarr[] = $row;
                } 
                return $jarr;
            case 'get_foreign_keys':
                while($row = mysqli_fetch_array($result))
                {
                    $jarr[] = $row;
                }
            case 'get_FK_vals':
                return $jarr;  
            case 's_query':
                
                if(!$row = mysqli_fetch_array($result))
                {
                    $jarr = array( 'empty_set' => 'Query returned no results');
                }
                else
                {
                    $jarr[] = $row;
                }
                
                while($row = mysqli_fetch_array($result))
                {
                    $jarr[] = $row;                
                }                                       
                return $jarr;     
            case 'del_row':
                while($row = mysqli_fetch_array($result))
                {
                    $jarr[] = $row;
                }
                return $jarr;  
            default:
                break;
        }       
    }
     
    function get_key_table()
    {
        "SELECT
                concat(table_name, '.', column_name) as 'foreign key', 
                concat(referenced_table_name, '.', referenced_column_name) as 'references'
        from
            information_schema.key_column_usage
        where
            referenced_table_name is not null";
    }
    
    function makeInsertQ()
    {
        $result = $mysqli->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ".$_REQUEST['table_name']." ORDER BY ordinal_position");
        $qStr = "INSERT INTO `".$_REQUEST['table_name']."` (";
        $rArr; $fCount;
                
        while($row = mysqli_fetch_array($result))
        {
            $rArr[] = $row[0];
        }      
        
        $fCount = count($rArr);
        
        for($i = 0; $i < $fCount; $i++)
        {
            if($i === 0)
            {
                if(ent_or_rel($_REQUEST['table_name'] === 'ent'))
                {
                    continue;
                }
            }
            
            if($i !== ($fCount - 1))
            {
                $qStr.'`'.$rArr[i].'`, ';
            }
            else
            {
                $qStr.'`'.$rArr[i].'` ';
            }
        }
        $qStr.') VALUES (';
        
        for($i = 0; $i < $fCount; $i++)
        {
            if($i === 0)
            {
                if(ent_or_rel($_REQUEST['table_name'] === 'ent'))
                {
                    continue;
                }
            }
            
            $val = $_REQUEST[$rArr[i]];
            
            if(is_numeric($val))
            {
                $qStr.$val;
            }
            else
            {
                $qStr.'\''.$val.'\'';
            }
            
            if($i !== ($fCount - 1))
            {
                $qStr.',';
            }
            else
            {
                $qStr.' ';
            }
        }  
        
        $qStr.')';
    }
    
    function ent_or_rel($t_name)
    {
        if($t_name === 'CharLocs' || $t_name === 'ClassAbl' || $t_name === 'ClassEqp')
        {
            return 'rel';
        }
        else
        {
            return 'ent';
        }
    }
?>