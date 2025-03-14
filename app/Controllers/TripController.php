<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\CustomerModel;
use App\Models\TripModel;

class TripController extends Controller
{
    public function submit()
    {
        $customerModel = new CustomerModel();
        $tripModel = new TripModel();

        $data = $this->request->getPost();
        $isAdmin = session()->get('is_admin');  // Assume you store whether the user is an admin in the session


        // Insert Customer
        $customerData = [
            'name' => $data['contact_name'],
            'number' => $data['contact_number']
        ];

        $customerId = $customerModel->insert($customerData);

        // Insert Trip
        $tripData = [
            'customer_id' => $customerId,
            'from_city' => $data['from'],
            'to_city' => $data['to'],
            'material' => $data['material'],
            'weight' => $data['weight'],
            'requested' => true,
            'accepted' => false
        ];

        if ($isAdmin) {
            // Admin submission: Add driver ID and set accepted to true, requested to false
            $tripData['driver_id'] = $data['driver_id']; // Assuming the driver ID is sent in the request
            $tripData['requested'] = false;
            $tripData['accepted'] = true;
        } else {
            // Customer submission: Set driver ID to null and requested to true, accepted to false
            $tripData['driver_id'] = null;
            $tripData['requested'] = true;
            $tripData['accepted'] = false;
        }

        $tripModel->insert($tripData);

        // Send Email
        $this->sendEmail($data);

        return redirect()->to('/')->with('success', 'Trip Request Submitted Successfully!');
    }

    private function sendEmail($data)
    {
        $email = \Config\Services::email();
        $email->setTo('viewvivek93@gmail.com');
        $email->setSubject('New Trip Request');
        $email->setMessage("From: {$data['from']}\nTo: {$data['to']}\nContact Name: {$data['contact_name']}\nContact Number: {$data['contact_number']}");
        $email->send();
    }
}
