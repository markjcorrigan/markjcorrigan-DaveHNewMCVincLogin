<h1><?= $product["name"] ?></h1>

<p><?= $product["description"] ?></p>

<p><a href="/products/<?= $product["id"] ?>/edit">Edit</a></p>

<p><a href="/products/<?= $product["id"] ?>/delete">Delete</a></p>

</body>
</html>