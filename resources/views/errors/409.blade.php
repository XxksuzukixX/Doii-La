@extends('errors::minimal')

@section('title', 'Conflict')
@section('code', '409')
@section('message', $exception->getMessage() ?: '処理が競合しました')