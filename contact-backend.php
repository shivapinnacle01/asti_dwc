<?php
if (isset($_POST)) {
    echo "<pre>";print_r($_POST); die;
        $data =   array(
            array(
                "Attribute" =>  "EmailAddress",
                "Value" => $_POST['email']
            ),
            array(
                "Attribute" =>  "FirstName",
                "Value" => $_POST['firstname']
            ),  
            array(
                "Attribute" =>  "Phone",
                "Value" =>  $_POST['phone']
            ),
            array(
                "Attribute" =>  "mx_Enrollment_Title_Qualification_Programmes",
                "Value" => $_POST['enrollment_title_qualification_program']
            ),
            array(
                "Attribute" =>  "mx_Country",
                "Value" => $_POST['country']
            ),
            array(
                "Attribute" =>  "Source",
                "Value" => 'Google Ads'
            ),
            
        );
     
    
    try{
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api-in21.leadsquared.com/v2/LeadManagement.svc/Lead.Create?accessKey=u$r62d0b5c22468bc1fcc43211bfb74fb53&secretKey=e7ba21fc61e459866f16f170bc7e66300a2aa167',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
        ),
       ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response_data = json_decode($response, true); 
    //   echo "<pre>";print_r($response_data); die;
        if($response_data['Status'] == 'Success'){
            echo json_encode( array('code' => 'Success')); die;
        }else{
             
            echo json_encode( array('code' => 'Error', 'message'=> $response_data['ExceptionMessage'])); die;
        }
        
    }catch(Exception $ex){
        curl_close($curl);
    }
         
} else {
    header("Location: index.php");
    exit;
}
?>