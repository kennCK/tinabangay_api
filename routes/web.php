<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
$route = env('PACKAGE_ROUTE', '');
Route::get('/', function () {
    return "heel";//view('welcome');
});
/*
  Accessing uploaded files
*/
Route::get($route.'/storage/profiles/{filename}', function ($filename)
{
    $path = storage_path('/app/profiles/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});
Route::get($route.'/storage/logo/{filename}', function ($filename)
{
    $path = storage_path('/app/logos/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});

Route::get('/cache', function () {
    $exitCode = Artisan::call('config:cache');
    return 'hey'.$exitCode;

    //
});
Route::get('/clear', function () {
    $exitCode = Artisan::call('config:cache');
    return 'hey'.$exitCode;

    //
});
Route::get('/migrate', function () {
    $exitCode = Artisan::call('migrate');
    return 'hey'.$exitCode;

    //
});

/* Authentication Router */
$route = env('PACKAGE_ROUTE', '').'/authenticate';
Route::resource($route, 'AuthenticateController', ['only' => ['index']]);
Route::post($route, 'AuthenticateController@authenticate');
Route::post($route.'/user', 'AuthenticateController@getAuthenticatedUser');
Route::post($route.'/refresh', 'AuthenticateController@refreshToken');
Route::post($route.'/invalidate', 'AuthenticateController@deauthenticate');
Route::post($route.'/auth', function () {
    return true;
});

//Emails Controller
$route = env('PACKAGE_ROUTE', '').'/emails';
Route::post($route.'/create', "EmailController@create");
Route::post($route.'/retrieve', "EmailController@retrieve");
Route::post($route.'/update', "EmailController@update");
Route::post($route.'/delete', "EmailController@delete");
Route::post($route.'/reset_password', 'EmailController@resetPassword');
Route::post($route.'/verification', 'EmailController@verification');
Route::post($route.'/changed_password', 'EmailController@changedPassword');
Route::post($route.'/referral', 'EmailController@referral');
Route::post($route.'/trial', 'EmailController@trial');
Route::post($route.'/alert', 'EmailController@alert');
Route::post($route.'/test_sms', 'EmailController@testSMS');

//Notification Settings Controller
$route = env('PACKAGE_ROUTE', '').'/notification_settings/';
$controller = 'NotificationSettingController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update_otp', $controller."generateOTP");
Route::post($route.'block_account', $controller."blockedAccount");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');


//Qr Code Controller
$route = env('PACKAGE_ROUTE', '').'/qr_codes/';
$controller = 'QrCodeController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'generate', $controller. 'generate');
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');

//Temperature Controller
$route = env('PACKAGE_ROUTE', '').'/temperatures/';
$controller = 'TemperatureController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'retrieve_tracing', $controller."retrieveTracing");
Route::post($route.'summary', $controller."summary");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');

//Temperature Location Controller
$route = env('PACKAGE_ROUTE', '').'/temperature_locations/';
$controller = 'TemperatureLocationController@';
Route::post($route.'create', $controller."create");
Route::get($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');

//Visited Places Controller
$route = env('PACKAGE_ROUTE', '').'/visited_places/';
$controller = 'VisitedPlaceController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'retrieve_tracing', $controller."retrieveTracing");
Route::post($route.'retrieve_customers', $controller."retrieveCustomers");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');

//Patients Controller
$route = env('PACKAGE_ROUTE', '').'/patients/';
$controller = 'PatientController@';
Route::post($route.'create', $controller."create");
Route::post($route.'linking', $controller."linking");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'retrieve_notifications', $controller."retrieveNotifications");
Route::post($route.'summary', $controller."summary");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');


//Tracing Controller
$route = env('PACKAGE_ROUTE', '').'/tracings/';
$controller = 'TracingController@';
Route::post($route.'tree', $controller."tree");
Route::post($route.'status', $controller."getStatus");

//Tracing Controller
$route = env('PACKAGE_ROUTE', '').'/tracing_places/';
$controller = 'TracingPlaceController@';
Route::post($route.'places', $controller."places");

//Google Places Controller
$route = env('PACKAGE_ROUTE', '').'/google_places/';
$controller = 'GooglePlaceController@';
Route::post($route.'search', $controller."search");

//Transportation Controller
$route = env('PACKAGE_ROUTE', '').'/transportations/';
$controller = 'TransportationController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'retrieve_tracing', $controller."retrieveTracing");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');

//Ride History Controller
$route = env('PACKAGE_ROUTE', '').'/ride_history/';
$controller = 'RideHistoryController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');

//Ride Controller
$route = env('PACKAGE_ROUTE', '').'/rides/';
$controller = 'RideController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');

//Transporation Mapping Controller
$route = env('PACKAGE_ROUTE', '').'/transportationmapping/';
$controller = 'TransportationMappingController@';
Route::post($route.'map', $controller.'map');

//Location Controller
$route = env('PACKAGE_ROUTE', '').'/locations/';
$controller = 'LocationController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'retrieve_locations_only', $controller."retrieveLocationsOnly");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');

//GoogleSheetController Controller
$route = env('PACKAGE_ROUTE', '').'/exports/';
$controller = 'GoogleSheetController@';
Route::post($route.'patients', $controller.'patients');

//Linked Account Controller
$route = env('PACKAGE_ROUTE', '').'/linked_accounts/';
$controller = 'LinkedAccountController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'retrieve_tracing', $controller."retrieveTracing");
Route::post($route.'retrieve_employees', $controller."retrieveEmployees");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');


//Linked Account Controller
$route = env('PACKAGE_ROUTE', '').'/symptoms/';
$controller = 'SymptomController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'retrieve_tracing', $controller."retrieveTracing");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');

//Barangay Codes Controller
$route = env('PACKAGE_ROUTE', '').'/brgy_codes/';
$controller = 'BrgyCodeController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');

//Custom Controller
$route = env('PACKAGE_ROUTE', '').'/customs/';
$controller = 'CustomController@';
Route::post($route.'import_accounts', $controller."importAccounts");
Route::post($route.'import_symptoms', $controller."importSymptoms");
Route::post($route.'import_visited_places', $controller."importVisitedPlaces");
Route::post($route.'set_address', $controller."setBrgyAddress");

//Complaints Controller
$route = env('PACKAGE_ROUTE', '').'/complaints/';
$controller = 'ComplaintController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');

//Consent Controller
$route = env('PACKAGE_ROUTE', '').'/consents/';
$controller = 'ConsentController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');

//Health Declarations Controller
$route = env('PACKAGE_ROUTE', '').'/health_declarations/';
$controller = 'HealthDeclarationController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');

//Posts Controller
$route = env('PACKAGE_ROUTE', '').'/posts/';
$controller = 'PostController@';
Route::post($route.'create', $controller."create");
Route::post($route.'retrieve', $controller."retrieve");
Route::post($route.'update', $controller."update");
Route::post($route.'delete', $controller."delete");
Route::get($route.'test', $controller.'test');
