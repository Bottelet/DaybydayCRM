<?php

namespace App\Models\Dashboard;

class DashboardStats {
    public $nb_clients;
    public  $nb_projects;
    public $nb_tasks;
    public  $nb_offers;
    public $nb_offers_conclude;
    public  $nb_offers_decline;
    public $nb_offers_progress;
    public  $nb_invoices;
    public $nb_payment;

    public function __construct(
        int $nb_clients = 0,
        int $nb_projects = 0,
        int $nb_tasks = 0,
        int $nb_offers = 0,
        int $nb_offers_conclude = 0,
        int $nb_offers_decline = 0,
        int $nb_offers_progress = 0,
        int $nb_invoices = 0,
        int $nb_payment = 0,
        int $nb_leads = 0
    ) {
        $this->nb_clients = $nb_clients;
        $this->nb_projects = $nb_projects;
        $this->nb_tasks = $nb_tasks;
        $this->nb_offers = $nb_offers;
        $this->nb_offers_conclude = $nb_offers_conclude;
        $this->nb_offers_decline = $nb_offers_decline;
        $this->nb_offers_progress = $nb_offers_progress;
        $this->nb_invoices = $nb_invoices;
        $this->nb_payment = $nb_payment;
        $this->nb_leads = $nb_leads;
    }
}
?>
