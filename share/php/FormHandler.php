<?php

# This library contains specific function the properly create messages for formHandler.js

# Check to see if function is in the post, and execute that function.
class FormHandler 
{ 

    public function handleForm()
    {
        if (isset($_POST['function']))
        {
            header('Content-Type: application/json');

            $formFunction = $_POST['function'];
            $data = $_POST['data'];
            
            // Get all user defined functions
            $userFunctions = get_defined_functions()["user"];
            
            // Make sure the requested user function is in the list
            // the array values are all cast to lower case, so check lower to lower string.
            if (in_array (strtolower($formFunction),$userFunctions) == true) {
                $result = call_user_func($formFunction,$data);

                $this->handleReturnMessage($result);
                echo json_encode($result);
            } else {
                echo '{"success":false,"title":"Failure!","message":"Unknown Function."}';
            }
            exit;
        }
    }

    # handleReturnMessagge will format a message suited for formHandler.js
    private function handleReturnMessage(&$array)
    {
        if (array_key_exists(0,$array))
        {
            if (is_array($array[0]) == true AND $array[0][0] == true) {
                $array['success'] = true;
                $array['message'] = $array['successmesssage'];
                $array['title'] = "Success";
            } else {
                $array['success'] = false;
                $array['message'] = nl2br($array[2]);
                $array['title'] = 'ERROR CODE:'.$array[0];
            }
        }
    }
}
    
?>