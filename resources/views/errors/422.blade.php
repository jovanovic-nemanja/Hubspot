@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '422')
@section('message', __($exception->getMessage() ?: 'Forbidden'))
