{% extends "base.mvc.php" %}

{% block title %}Home{% endblock %}

{% block body %}

<h1>Home Page</h1>

<a href="/signup/new">Click here to register</a><br><br>

<a href="/signup">Signup</a><br>
<a href="/login">Login</a><br><br>

<a href="/products/index">Click here for the products section</a><br>

{% endblock %}