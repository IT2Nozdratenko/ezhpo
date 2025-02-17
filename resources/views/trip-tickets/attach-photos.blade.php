@extends('layouts.app')

@section('title', 'Загрузка фото ПЛ')

@php

@endphp

@section('custom-styles')
    <style>
        label {
            font-size: 18px;
            margin-right: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6 offset-3">
            <div class="card">
                <div class="card-body">
                    <p><b>Загрузка фото ПЛ</b></p>
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('trip-tickets.attach-photos', ['id' => $id]) }}"
                          class="form-horizontal"
                          onsubmit="document.querySelector('#page-preloader').classList.remove('hide')"
                          enctype="multipart/form-data">
                        @csrf

                        <attach-photos-index
                            :id="'{{ $id }}'"
                            :items="JSON.parse('{{ json_encode($photos) }}')"
                        ></attach-photos-index>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
