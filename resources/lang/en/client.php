<?php

return [

    'status' => [
        'assigned' => 'Assigned Clients',
    ],

    'titles' => [
    	'create' => 'Create New Client',
    	'update' => 'Update client',
    ],

    'headers' => [
        'name' => 'Name',
        'company' => 'Company',
        'mail' => 'Email',
        'primary_number' => 'Number',
        'secondary_number' => 'Seconday number',
        'full_address' => 'Address / Zip code / City',
        'vat' => 'VAT number',
        'industry' => 'Industry',
        'company_type' => 'Company type',
    ],

    'tabs' => [
    	'tasks' => 'Tasks',
    	'leads' => 'Leads',
    	'documents' => 'Documents',
    	'invoices' => 'Invoices',
    	'all_tasks' => 'All tasks',
    	'all_leads' => 'All leads',
    	'all_documents' => 'All documents',
    	'max_size' => 'Max 5MB pr. file',
    	//Headers on tables in tables
    		'headers' => [
    		//Title && Leads
    			'title' => 'Title',
    			'assigned' => 'Assigned user',
    			'created_at' => 'Created at',
    			'deadline' => 'Deadline',
    			'new_task' => 'Add new task',
    			'new_lead' => 'Add new lead',
    			//Documments
		    	'file' => 'File',
    			'size' => 'Size',
    			//Invoices
    			'id' => 'ID',
    			'hours' => 'Hours',
    			'total_amount' => 'Total amount',
    			'invoice_sent' => 'Invoice sent',
    			'payment_received' => 'Payment received',
    		],
    ],
];
