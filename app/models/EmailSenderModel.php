<?php

namespace App\Models;

use Exception;
use SendGrid;
use SendGrid\Mail\Mail;

class EmailSenderModel
{
  public function sendEmail($subject, $to, $content)
  {
    $email = new Mail(); 
    $email->setFrom("rsystfip@gmail.com", "RSystfip");
    $email->setSubject($subject);
    $email->addTo($to);
    $email->addContent("text/html", "<strong>$content</strong>");
    
    $envModelInstance = new EnvModel();
    $sendgridApiKey = $envModelInstance->reader('SENDGRID_API_KEY');
    $sendgrid = new SendGrid($sendgridApiKey);
    
    try {
      $response = $sendgrid->send($email);
      print $response->statusCode() . "\n";
      print_r($response->headers());
      print $response->body() . "\n";
    } catch (Exception $e) {
      echo 'Caught exception: '. $e->getMessage() ."\n";
    }
  }
}