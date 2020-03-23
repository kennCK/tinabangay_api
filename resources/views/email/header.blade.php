<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{env('APP_NAME')}}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }
            .header, .text{
                width: 90%;
                float:left;
                min-height: 10px;
                overflow-y: hidden;
                margin: 0 5% 0 5%;
            }
            .header{
                margin-top: 25px;
            }
            .header img{
                float: left;
                margin-right: 10px;
            }
            .header span{
                font-size: 40px;
                font-weight: 600;
                float: left;
            }
            .text-primary{
                color:#005b96;   
            }
            .text-secondary{
                color:#005b96;
            }
            .text{
                color: #555;
                font-size: 18px;
            }
            .holder{
                width: 100%;
                float: left;
                margin-top: 25px;
                margin-bottom: 50px;
            }
            .holder .thank-you-header{
                height: 200px;
                width: 100%;
                float: left;
                background: #005b96;
                color: #fff;
                text-align: center;
                border-top-right-radius: 5px;
                border-top-left-radius: 5px;
            }
            .holder .thank-you-header h1, .holder .thank-you-header h2{
                width: 100%;
                float: left;
            }
            .holder .thank-you-header label{
                float: left;
                width: 100%;
            }

            .thank-you-item{
                height: 50px;
                width: 100%;
                float: left;
                line-height: 50px;
                border-bottom: solid 1px #eee;
                padding-left: 10px;
            }
            .thank-you-item label{
                width: 25%;
                float: left;
            }
            .button{
                width: 300px;
                height: 50px;
                background: #005b96;
                border: none;
                color: #fff;
                font-size: 16px;
                margin-top: 25px;
                margin-bottom: 25px;
                border-radius: 5px;
            }
            .button:hover{
                cursor: pointer;
            }
            .footer{
                width: 100%;
                margin: 25px 0 25px 0;
                border-top: solid 1px #aaa;
                text-align: center;
                float: left;
            }
            .footer label{
                margin-top: 10px;
                color: #555;
            }
        </style>
    </head>
    <body>
		<span class="header">
		    <img src="{{env('APP_URL')}}{{env('PACKAGE_ROUTE')}}/storage/logo/logo.png" height="60px" width="60px">
		    <span><label class="text-secondary">{{env('APP_NAME')}}</label></span>
		</span>