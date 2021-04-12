<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});



$router->group(['prefix' => 'api'], function ($router) {
    //auth routes
    $router->post('/userRegister', 'AuthController@register');
    $router->post('/login', 'AuthController@authenticate');
    $router->post('/logout', 'AuthController@logout');
    $router->post('/change-password', 'AuthController@changePassword');

    //employer routes
    $router->get('/employer', 'EmployerController@index');
    $router->get('employer/edit-profile', 'EmployerController@show');
    $router->post('/employer/edit-profile', 'EmployerController@update');

    $router->get('employer/view-posted-jobs', 'EmployerController@viewPostedJobs');
    $router->get('employer/view-job-applicants', 'EmployerController@viewJobApplicants');
    $router->post('employer/post-new-job', 'EmployerController@createJob');
    $router->delete('employer/delete-job/{id}', 'EmployerController@destroyJob');

    //jobseeker routes
    $router->get('/jobseeker', 'JobseekerController@index');
    $router->get('/jobseeker/edit-profile', 'JobseekerController@show');
    $router->post('/jobseeker/edit-profile', 'JobseekerController@update');
    $router->post('/downloadCV', 'JobseekerController@downloadCV');

    //admin routes
    $router->get('/admin', 'AdminController@index');
    $router->get('/admin/jobseekers', 'AdminController@viewJobApplicants');
    $router->get('/admin/employers', 'AdminController@viewEmployers');
    $router->post('/admin/jobseeker/{jobApplicantId}/change-status', 'AdminController@changeJobApplicantStatus');
    $router->post('/admin/employer/{employerId}/change-status', 'AdminController@changeEmployerStatus');

    // page routes
    $router->get('/home', 'PageController@home');
    $router->get('/mobile-app-home', 'PageController@mobileAppHome');
    $router->post('/apply-for-job', 'PageController@applyForJob');
    $router->get('/job/{slug}', 'PageController@viewJob');
    $router->post('/search', 'PageController@filterJobs');
    $router->post('/mobile-search', 'PageController@filterJobs');
    
});
