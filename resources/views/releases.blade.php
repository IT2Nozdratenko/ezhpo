@extends('layouts.app')

@section('title','Профиль')
@section('sidebar', 1)

@section('content')

    <div class="row">
        <!-- Form Elements -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h2>Релизы ЭЖПО</h2>

                    <article>
                        <hr>
                        <h4>0.9.11</h4>
                        <p>релиз последних доработок, включая правки связанные с СДПО</p>
                    </article>

                    <article>
                        <hr>
                        <h4>0.9.10</h4>
                        <p>релиз новых пуллов</p>
                    </article>

                    <article>
                        <hr>
                        <h4>0.9.9</h4>
                        <p>переработаны доработки от 18.04.2022 г., выполнен состав новых доработок</p>
                    </article>

                    <article>
                        <hr>
                        <h4>0.9.8</h4>
                        <p>переработаны доработки от 15.04.2022 г., установлен CRM.TA-7.RU на VDS-сервер</p>
                    </article>

                    <article>
                        <hr>
                        <h4>0.9.7</h4>
                        <p>переработаны доработки по отчетам, личным кабинетам и осмотрам</p>
                    </article>
                </div>
            </div>
        </div>
    </div>

@endsection
