<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Createalltables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function(Blueprint $table){
            $table->id();
            $table->string('name');            
            $table->string('email')->unique();
            $table->string('cpf')->unique();
            $table->string('password');
        });

        Schema::create('units', function(Blueprint $table){
            $table->id();
            $table->string('name');            
            $table->integer('id_owner');
        });

        Schema::create('unitpeoples', function(Blueprint $table){
            $table->id();                        
            $table->integer('id_unit');
            $table->string('name');
            $table->date('birthdate');
        });

        Schema::create('unitvehicles', function(Blueprint $table){
            $table->id();                        
            $table->integer('id_unit');
            $table->string('brand');
            $table->string('model');
            $table->string('color');
            $table->string('plate');
        });

        Schema::create('unitpets', function(Blueprint $table){
            $table->id();                        
            $table->integer('id_unit');
            $table->string('name');
            $table->string('race');
        });

        Schema::create('walls', function(Blueprint $table){
            $table->id();                        
            $table->string('title');
            $table->string('body');
            $table->datetime('datecreated');
        });

        Schema::create('walllikes', function(Blueprint $table){
            $table->id();
            $table->integer('id_wall');
            $table->integer('id_user');                       
            $table->datetime('datecreated');
        });

        Schema::create('docs', function(Blueprint $table){
            $table->id();                      
            $table->string('title');
            $table->string('fileurl');
            $table->datetime('datecreated');
        });

        Schema::create('billets', function(Blueprint $table){
            $table->id();         
            $table->integer('id_unit');             
            $table->string('title');
            $table->string('fileurl');
            $table->datetime('datecreated');
        });

        Schema::create('warnings', function(Blueprint $table){
            $table->id();         
            $table->integer('id_unit');             
            $table->string('title');
            $table->string('status')->default('IN_REVIEW');
            $table->text('photos');
            $table->datetime('datecreated');
        });

        Schema::create('foundandlost', function(Blueprint $table){
            $table->id();         
            $table->string('status')->default('LOST');             
            $table->string('photo');
            $table->string('description');
            $table->string('where');
            $table->datetime('datecreated');
        });

        Schema::create('areas', function(Blueprint $table){
            $table->id();         
            $table->integer('allowed')->default(1);             
            $table->string('title');
            $table->string('cover');
            $table->string('days');
            $table->time('start_time');
            $table->time('end_time');
            $table->datetime('datecreated');
        });

        Schema::create('areadisableddays', function(Blueprint $table){
            $table->id();         
            $table->integer('id_area');             
            $table->date('day');
            $table->datetime('datecreated');
        });

        Schema::create('reservatins', function(Blueprint $table){
            $table->id();         
            $table->integer('id_unit');
            $table->integer('id_area');          
            $table->datetime('reservation_date');
            $table->datetime('datecreated');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('units');
        Schema::dropIfExists('unitpeoples');
        Schema::dropIfExists('unitvehicles');
        Schema::dropIfExists('unitpets');
        Schema::dropIfExists('walls');
        Schema::dropIfExists('walllikes');
        Schema::dropIfExists('docs');
        Schema::dropIfExists('billets');
        Schema::dropIfExists('warnings');
        Schema::dropIfExists('foundandlost');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('areadisabledays');
        Schema::dropIfExists('reservations');
    }
}
