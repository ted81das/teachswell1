<?php

require_once ABSPATH.'/wp-content/plugins/xcloner-backup-and-restore/vendor/autoload.php'; // Include the AWS SDK for PHP
use Aws\S3\S3Client;


// Set your AWS credentials and region
$accessKeyId = defined('MAADPRO_AWS_ACCESS_KEY_ID') ? MAADPRO_AWS_ACCESS_KEY_ID : 'YOUR_ACCESS_KEY_ID';
$secretAccessKey = defined('MAADPRO_AWS_SECRET_ACCESS_KEY') ? MAADPRO_AWS_SECRET_ACCESS_KEY : 'YOUR_SECRET_ACCESS_KEY';
$region = 'us-west-1';
$use_path_style_endpoint = true;
$endpoint = 'https://s3.eu-central-1.wasabisys.com';

// Create an S3 client
$client = new S3Client([
    'endpoint' => 'https://s3.eu-central-1.wasabisys.com', // please refer to service end points for buckets in different regions
    'region' => 'eu-central-1', // please refer to service end points for buckets in different regions
    'version' => 'latest',
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key' => $accessKeyId,
        'secret' => $secretAccessKey,
    ],
]);

/*
// use the following code to establish a connection to Wasabi via a profile in the .aws credential files.
$profile_credentials = array(
    'profile' => 'wasabi',
    'endpoint' => 'https://s3.wasabisys.com', // please refer to service end points for buckets in different regions
    'region' => 'us-east-1', // please refer to service end points for buckets in different regions
    'version' => 'latest',
    'use_path_style_endpoint' => true
);
*/

// Get the folder name from WP_CONFIG constant in wp-config.php
    $folderName = defined('MEDIA_FOLDER_PREFIX') ? MEDIA_FOLDER_PREFIX : 'unique2';

$bucketName = 'ewwwagencyassets';
//$maxTotalSize = 25 * 1024 * 1024 * 1024; // 25 GB in bytes
$maxTotalSize = 35 * 1024 * 1024; // 35 MB in bytes

// Get the list of objects in the folder
$result = $client->listObjects([
    'Bucket' => $bucketName,
    'Prefix' => $folderName,
]);



/* 
$fileCount = count($result['Contents']);
$totalSize = 0;

// Calculate the total size of files in the folder
foreach ($result['Contents'] as $object) {
    $totalSize += $object['Size'];
}
*/

// Prevent further uploads if total size exceeds 25 GB
/*if ($totalSize > $maxTotalSize) {
    die('Upload limit exceeded. Please contact the administrator.');
}
*/
// Continue with the file upload process
// ...


// Register the wp_handle_upload_prefilter filter
add_filter('wp_handle_upload_prefilter', 'wpturbo_prevent_upload');

function wpturbo_prevent_upload($file) {
    global $client, $bucketName, $folderName, $maxTotalSize;

    // Get the list of objects in the folder
    $result = $client->listObjects([
        'Bucket' => $bucketName,
        'Prefix' => $folderName,
    ]);

    $totalSize = 0;

    // Calculate the total size of files in the folder
    foreach ($result['Contents'] as $object) {
        $totalSize += $object['Size'];
    }

    if ($totalSize > $maxTotalSize) {
        $file['warning'] = 'You are approaching your limit, please free up some space on your cloud bucket.';
    }
    
    $hardLimit = $maxTotalSize + 15000000;
    // Prevent further uploads if total size exceeds 25 GB
    if ($totalSize > $hardLimit) {
        $file['error'] = 'Upload limit exceeded.'.$totalSize.'    '.$hardLimit.'Please contact the administrator.';
    }

    return $file;
}

//add_filter('wp_handle_upload_prefilter', 'ae_file_upload_warning');


function ae_file_upload_warning() {
	    global $client, $bucketName, $folderName, $maxTotalSize;

    // Get the list of objects in the folder
    $result = $client->listObjects([
        'Bucket' => $bucketName,
        'Prefix' => $folderName,
    ]);

    $totalSize = 0;

    // Calculate the total size of files in the folder
    foreach ($result['Contents'] as $object) {
        $totalSize += $object['Size'];
    }

        $warning_text = 'You are currently at '.$totalSize.' bytes.  Your total limit is '.$maxTotalSize.' bytes.  Please free up some space on your cloud bucket to avoid surprises.';
    
    $hardLimit = $maxTotalSize + 15000000;

	
	echo '<style>.notice {	background-color: rgb(255, 255, 224); border: 1px solid rgb(230, 219, 85); margin: 5px 0 15px;  } </style>
		<div id="message" class="notice">
			<p style="padding: 0 10px;"><strong>'. $warning_text .'
			</strong></p>
			<p style="padding: 0 10px;">Available tools to resize photos:
			<ul style="padding-left: 40px; list-style-type: square; ">
				<li><a href="http://advanced.aviary.com/tools/image-editor">Aviary</a></li>
				<li><a href="http://picasa.google.com/">Picasa</a></li>
			</ul>
			</p>
		</div>';
}
add_action( 'post-upload-ui', 'ae_file_upload_warning' );
