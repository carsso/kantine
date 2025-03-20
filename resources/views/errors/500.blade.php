@extends('errors::minimal')

@section('title', __('Server Error'))
@section('code', '500')
@section('message', env('APP_ENV') == 'production' ? __('Server Error') : ($exception->getMessage() ?: __('Server Error')))
