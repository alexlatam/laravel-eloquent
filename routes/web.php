<?php

use Illuminate\Support\Facades\Route;
use App\Models\Billing;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

/**
 * BUSCA UN POST POR SU ID
 */
Route::get("/find/{id}", function (int $id) {
    return Post::find($id);
});


/**
 * BUSCA UN POST POR SU ID O RETORNA UNA INSTANCIA VACIA DEL MODELO POST. NO PERSISTE DATA EN LA BD
 */
Route::get("/find-or-new/{id}", function (int $id) {
    try {
        return Post::findOrNew($id);
    } catch (ModelNotFoundException $exception) {
        return $exception->getMessage();
    }
});

/**
 * BUSCA EL PRIMER POST QUE CUMPLA UNA CONDICION. SINO, RETORNA UNA INSTANCIA DEL MODELO POST CON LOS VALORES DE LA CONDICION. NO PERSISTE DATA EN LA BD
 */
Route::get("/first-or-new/{title}", function (string $title) {
    try {
        return Post::firstOrNew(["title" => $title]);
    } catch (ModelNotFoundException $exception) {
        return $exception->getMessage();
    }
});

/**
 * BUSCA EL PRIMER POST QUE CUMPLA UNA CONDICION. SINO, RETORNA UNA INSTANCIA DEL MODELO POST, YA PERSISTIDO, CON LOS VALORES DE LA CONDICION. SI PERSISTE DATA EN LA BD
 */
Route::get("/first-or-create/{title}", function (string $title) {
    try {
        return Post::firstOrCreate(["title" => $title]);
    } catch (ModelNotFoundException $exception) {
        return $exception->getMessage();
    }
});

/**
 * BUSCA UN POST POR SU ID O RETORNA UN 404
 */
Route::get("/find-or-fail/{id}", function (int $id) {
    try {
        return Post::findOrFail($id);
    } catch (ModelNotFoundException $exception) {
        return $exception->getMessage();
    }
});


/**
 * BUSCA UN POST POR SU ID Y SELECCIONA COLUMNAS O RETORNA UN 404
 */
Route::get("/find-or-fail-with-columns/{id}", function (int $id) {
    return Post::findOrFail($id, ["id", "title"]);
});

/**
 * BUSCA UN POST POR SU SLUG O RETORNA UN 404
 */
Route::get("/find-by-slug/{slug}", function (string $slug) {
    // en lugar de
    //return Post::where("slug", $slug)->firstOrFail();

    // podemos hacer esto
    // return Post::whereSlug($slug)->firstOrFail();

    // o mejor aún
    return Post::firstWhere("slug", $slug);
});

/**
 * BUSCA MUCHOS POSTS POR UN ARRAY DE IDS
 */
Route::get("/find-many", function () {
    // en lugar de esto
    //return Post::whereIn("id", [1, 2, 3])->get();

    // haz lo siguiente
    return Post::find([1, 2, 3], ["id", "title"]);
});

/**
 * POSTS PAGINADOS CON SELECCIÓN DE COLUMNAS
 */
Route::get("/paginated/{perPage}", function (int $perPage = 10) {
    return Post::paginate($perPage, ["id", "title"]);
});

/**
 * POSTS PAGINADOS MANUALMENTE CON OFFSET/LIMIT
 *
 * http://127.0.0.1:8000/manual-pagination/2 -> primera página
 * http://127.0.0.1:8000/manual-pagination/2/2 -> segunda página
 */
Route::get("/manual-pagination/{perPage}/{offset?}", function (int $perPage, int $offset = 0) {
    return Post::offset($offset)->limit($perPage)->get();
});

/**
 *  CREA UN POST
 */
Route::get("/create", function () {
    $user = User::all()->random(1)->first()->id;
    return Post::create([
        "user_id" => $user,
        "category_id" => Category::all()->random(1)->first()->id,
        "title" => "Post para el usuario {$user}",
        "content" => "Nuevo post de pruebas",
    ]);
});

/**
 * CREA UN POST O SI EXISTE RETORNARLO
 */
Route::get("/first-or-create", function () {
    return Post::firstOrCreate(
        ["title" => "Nuevo post para ordenación"],
        [
            "user_id" => User::all()->random(1)->first()->id,
            "category_id" => Category::all()->random(1)->first()->id,
            "title" => "Nuevo post para ordenación",
            "content" => "cualquier cosa",
        ]
    );
});

/**
 * BUSCA UN POST Y CARGA SU AUTOR, CATEGORÍA Y TAGS CON TODA LA INFORMACIÓN
 */
Route::get("/with-relations/{id}", function (int $id) {
    return Post::with("user", "category", "tags")->find($id);
});

/**
 * BUSCA UN POST Y CARGA SU AUTOR, CATEGORÍA Y TAGS CON TODA LA INFORMACIÓN UTILIZANDO LOAD
 */
Route::get("/with-relations-using-load/{id}", function (int $id) {
    $post = Post::findOrFail($id);
    $post->load("user", "category", "tags");
    return $post;
});

/**
 * BUSCA UN POST Y CARGA SU AUTOR, CATEGORÍA Y TAGS CON SELECCIÓN DE COLUMNAS EN RELACIONES
 */
Route::get("/with-relations-and-columns/{id}", function (int $id) {
    return Post::select(["id", "user_id", "category_id", "title"])
        ->with([
            "user:id,name,email",
            "user.billing:id,user_id,credit_card_number",
            "tags:id,tag",
            "category:id,name",
        ])
        ->find($id);
});

/**
 * BUSCA UN USUARIO Y CARGA EL NÚMERO DE POSTS QUE TIENE
 */
Route::get("/with-count-posts/{id}", function (int $id) {
    return User::select(["id", "name", "email"])
        ->withCount("posts")
        ->findOrFail($id);
});

/**
 * BUSCA UN POST O RETORNA UN 404, PERO SI EXISTE ACTUALÍZALO
 */
Route::get("/update/{id}", function (int $id) {
    // en lugar de hacer lo siguiente
    //$post = Post::findOrFail($id);
    //$post->title = "Post actualizado";
    //$post->save();
    //return $post;

    // haz lo siguiente
    return Post::findOrFail($id)->update([
        "title" => "Post actualizado de nuevo...",
    ]);
});

/**
 * ACTUALIZA UN POST EXISTENTE POR SU SLUG O LO CREA SI NO EXISTE
 */
Route::get("/update-or-create/{slug}", function (string $slug) {
    /* en lugar de
    $post = Post::whereSlug($slug)->first();
    if ($post) {
        $post->update([
            "user_id" => User::all()->random(1)->first()->id,
            "title" => "Post de pruebas",
            "content" => "haciendo algunas pruebas",
        ]);
    } else {
        $post = Post::create([
            "user_id" => User::all()->random(1)->first()->id,
            "title" => "Post de pruebas",
            "content" => "haciendo algunas pruebas",
        ]);
    }
    return $post;
    */

    // haz lo siguiente
    return Post::updateOrCreate(
        [
            "slug" => $slug,
        ],
        [
            "user_id" => User::all()->random(1)->first()->id,
            "category_id" => Category::all()->random(1)->first()->id,
            "title" => "Post de pruebas",
            "content" => "Nuevo contenido del post actualizado...."
        ],
    );
});

/**
 * ELIMINA UN POST Y SUS TAGS RELACIONADOS SI EXISTE
 */
Route::get("/delete-with-tags/{id}", function (int $id) {
    try {
        DB::beginTransaction();
        $post = Post::findOrFail($id);
        $post->tags()->detach();
        $post->delete();
        DB::commit();
        return $post;
    } catch (Exception $exception) {
        DB::rollBack();
        return $exception->getMessage();
    }
});

/**
 * BUSCA UN POST O RETORNA UN 404, PERO SI EXISTE DALE LIKE
 */
Route::get("/like/{id}", function (int $id) {
    // en lugar de
    // $post = Post::findOrFail($id);
    // $post->likes++;
    // $post->save();

    // haz lo siguiente
    return Post::findOrFail($id)->increment("likes", 20, [
        "title" => "Post con muchos likes",
    ]);
});

/**
 * BUSCA UN POST O RETORNA UN 404, PERO SI EXISTE DALE DISLIKE
 */
Route::get("/dislike/{id}", function (int $id) {
    // en lugar de
    // $post = Post::findOrFail($id);
    // $post->dislikes++;
    // $post->save();

    // haz lo siguiente
    return Post::findOrFail($id)->increment("dislikes");
});

/**
 * PROCESOS COMPLEJOS BASADOS EN CHUNCKS
 */
Route::get("/chunk/{amount}", function (int $amount) {
    Post::chunk($amount, function (Collection $chunk) {

    });
});

/**
 * CREA UN USUARIO Y SU INFORMACIÓN DE PAGO
 * SI EXISTE EL USUARIO LO UTILIZA
 * SI EXISTE EL MÉTODO DE PAGO LO ACTUALIZA
 */
Route::get("/create-with-relation", function () {
    try {
        DB::beginTransaction();
        $user = User::firstOrCreate(
            ["name" => "cursosdesarrolloweb"],
            [
                "name" => "cursosdesarrolloweb",
                "age" => 40,
                "email" => "eloquent@cursosdesarrolloweb.es",
                "password" => bcrypt("password"),
            ]
        );
        Billing::updateOrCreate(
            ["user_id" => $user->id],
            [
                "user_id" => $user->id,
                "credit_card_number" => "123456789"
            ]
        );
        DB::commit();
        return $user
            ->load("billing:id,user_id,credit_card_number");
    } catch (Exception $exception) {
        DB::rollBack();
        return $exception->getMessage();
    }
});

/**
 * ACTUALIZA UN POST Y SUS RELACIONES
 */
Route::get("/update-with-relation/{id}", function (int $id) {
    $post = Post::findOrFail($id);
    $post->title = "Post actualizado con relaciones";
    $post->tags()->attach(Tag::all()->random(1)->first()->id);
    $post->save();
});

/**
 * POSTS QUE TENGAN MÁS DE 2 TAGS RELACIONADOS
 */
Route::get("/has-two-tags-or-more", function () {
    return Post::select(["id", "title"])
        ->withCount("tags")
        ->has("tags", ">=", 3)
        ->get();
});

/**
 * BUSCA UN POST Y CARGA SUS TAGS ORDENADOS POR NOMBRE ASCENDENTEMENTE
 */
Route::get("/with-tags-sorted/{id}", function (int $id) {
    return Post::with("sortedTags:id,tag")
        ->find($id);
});


/**
 * BUSCA TODOS LOS POSTS QUE TENGAN TAGS
 */
Route::get("/with-where-has-tags", function () {
    return Post::select(["id", "title"])
        ->with("tags:id,tag")
        ->whereHas("tags")
        ->get();
});

/**
 * SCOPE PARA BUSCAR TODOS LOS POSTS QUE TENGAN TAGS
 */
Route::get("/scope-with-where-has-tags", function () {
    return Post::whereHasTagsWithTags()->get();
});

/**
 * BUSCA UN POST Y CARGA SU AUTOR DE FORMA AUTOMÁTICA Y SUS TAGS CON TODA LA INFORMACIÓN
 */
Route::get("/autoload-user-from-post-with-tags/{id}", function (int $id) {
    return Post::with("tags:id,tag")->findOrFail($id);
});

/**
 * POST CON ATRIBUTOS PERSONALIZADOS
 */
Route::get("/custom-attributes/{id}", function (int $id) {
    return Post::with("user:id,name")->findOrFail($id);
});

/**
 * BUSCA POSTS POR FECHA DE ALTA, VÁLIDO FORMATO Y-m-d
 *
 * http://127.0.0.1:8000/by-created-at/2021-08-05
 */
Route::get("/by-created-at/{date}", function (string $date) {
    return Post::whereDate("created_at", $date)
        ->get();
});

/**
 * BUSCA POSTS POR DÍA Y MES EN FECHA DE ALTA
 *
 * http://127.0.0.1:8000/by-created-at-month-day/05/08
 */
Route::get("/by-created-at-month-day/{day}/{month}", function (int $day, int $month) {
    return Post::whereMonth("created_at", $month)
        ->whereDay("created_at", $day)
        ->get();
});

/**
 * BUSCA POSTS EN UN RANGO DE FECHAS
 *
 * http://127.0.0.1:8000/between-by-created-at/2021-08-01/2021-08-05
 */
Route::get("/between-by-created-at/{start}/{end}", function (string $start, string $end) {
    return Post::whereBetween("created_at", [$start, $end])->get();
});

/**
 * OBTIENE TODOS LOS POSTS QUE EL DÍA DEL MES SEA SUPERIOR A 5 O UNO POR SLUG SI EXISTE LA QUERYSTRING SLUG
 *
 * http://127.0.0.1:8000/when-slug?slug=<slug>
 */
Route::get("/when-slug", function () {
    return Post::whereMonth("created_at", now()->month)
        ->whereDay("created_at", ">", 5)
        ->when(request()->query("slug"), function (Builder $builder) {
            $builder->whereSlug(request()->query("slug"));
        })
        ->get();
});

/**
 *
 * SUBQUERIES PARA CONSULTAS AVANZADAS
 *
 * select * from `users` where (`banned` = true and `age` >= 50) or (`banned` = false and `age` <= 30)
 */
Route::get("/subquery", function () {
    return User::where(function (Builder $builder) {
            $builder->where("banned", true)
                ->where("age", ">=", 50);
        })
        ->orWhere(function (Builder $builder) {
            $builder->where("banned", false)
                ->where("age", "<=", 30);
        })
        ->get();
});

/**
 * SCOPE GLOBAL EN POSTS PARA OBTENER SÓLO POSTS DE ESTE MES
 *
 */
Route::get("/global-scope-posts-current-month", function () {
    return Post::count();
});

/**
 * DESHABILITAR SCOPE GLOBAL EN POSTS PARA OBTENER TODOS LOS POSTS
 */
Route::get("/without-global-scope-posts-current-month", function () {
    return Post::withoutGlobalScope("currentMonth")->count();
});

/**
 * POSTS AGRUPADOS POR CATEGORÍA CON SUMA DE LIKES Y DISLIKES
 * ESTA CONSULTA RETORNA TODAS LAS CATEGORIAS QUE TENGAN AL MENOS UN POST Y EL TOTAL DE LOS LIKES Y DISLIKES POR CATEGORIA
 */
Route::get("/query-raw", function () {
    return Post::withoutGlobalScope("currentMonth")
        ->with("category")
        ->select([
            "id",
            "category_id",
            "likes",
            "dislikes",
            DB::raw("SUM(likes) as total_likes"),
            DB::raw("SUM(dislikes) as total_dislikes"),
        ])
        ->groupBy("category_id")
        ->get();
});

/**
 * POSTS AGRUPADOS POR CATEGORÍA CON SUMA DE LIKES Y DISLIKES QUE SUMEN MÁS DE 110 LIKES
 * ESTA CONSULTA RETORNA TODAS LAS CATEGORIAS QUE TENGAN AL MENOS UN POST Y EL TOTAL DE LOS LIKES Y DISLIKES POR CATEGORIA
 * Y ADICIONALMENTE FILTRA POR LA SUMA DE LIKES PARA RETORNAR SOLO LAS CATEGORIAS QUE TENGAN MÁS DE 110 LIKES
 */
Route::get("/query-raw-having-raw", function () {
    return Post::withoutGlobalScope("currentMonth")
        ->with("category")
        ->select([
            "id",
            "category_id",
            "likes",
            "dislikes",
            DB::raw("SUM(likes) as total_likes"),
            DB::raw("SUM(dislikes) as total_dislikes"),
        ])
        ->groupBy("category_id")
        ->havingRaw("SUM(likes) > ?", [110])
        ->get();
});

/**
 * USUARIOS ORDENADOS POR SU ÚLTIMO POST.
 * TODOS LOS USUARIOS QUE TENGAN UN POST, ORDENADOS DESDE EL USUARIO QUE HA CREADO EL POST MAS RECIENTE
 */
Route::get("/order-by-subqueries", function () {
    return User::select(["id", "name"])
        ->has("posts")
        ->orderByDesc(
            // Esta consulta obtiene el último post de cada usuario, especificamente la fecha de creación
            Post::withoutGlobalScope("currentMonth")
                ->select("created_at")
                ->whereColumn("user_id", "users.id")
                ->orderBy("created_at", "desc")
                ->limit(1)
        )
        ->get();
});

/**
 * USUARIOS QUE TIENEN POSTS CON SU ÚLTIMO POST PUBLICADO
 * OTIENE TODOS LOS USUARIOS QUE TENGAN UN POST, Y ADICIONALMENTE AGREGA EL TITULO DEL ULTIMO POST DE CADA USUARIO
 */
Route::get("/select-subqueries", function () {
    return User::select(["id", "name"])
        ->has("posts")
        ->addSelect([
            "last_post" => Post::withoutGlobalScope("currentMonth")
                ->select("title")
                ->whereColumn("user_id", "users.id")
                ->orderBy("created_at", "desc")
                ->limit(1)
        ])
        ->get();
});

/**
 * INSERT MASIVO DE USUARIOS
 */
Route::get("/multiple-insert", function () {
    $users = new Collection;
    for ($i = 1; $i <= 20; $i++) {
        $users->push([
            "name" => "usuario $i",
            "email" => "usuario$i@m.com",
            "password" => bcrypt("password"),
            "email_verified_at" => now(),
            "created_at" => now(),
            "age" => rand(20, 50)
        ]);
    }
    User::insert($users->toArray());
});


/**
 * INSERT BATCH
 * INSERTAR 150 USUARIOS EN 2 CONSULTAS DE 100 REGISTROS CADA UNA
 */
Route::get("/batch-insert", function () {
    $userInstance = new User;
    $columns = [
        'name',
        'email',
        'password',
        'age',
        'banned',
        'email_verified_at',
        'created_at'
    ];
    $users = new Collection;
    for ($i = 1; $i <= 150; $i++) {
        $users->push([
            "usuario $i",
            "usuario$i@m.com",
            bcrypt("password"),
            rand(20, 50),
            rand(0, 1),
            now(),
            now(),
        ]);
    }
    $batchSize = 100; // insert 500 (default), 100 minimum rows in one query

    /** @var Mavinoo\Batch\Batch $batch */
    $batch = batch();
    return $batch->insert($userInstance, $columns, $users->toArray(), $batchSize);
});

/**
 * UPDATE BATCH
 */
Route::get("/batch-update", function () {
    $postInstance = new Post;

    $toUpdate = [
        [
            "id" => 1,
            "likes" => ["*", 2], // multiplica
            "dislikes" => ["/", 2], // divide
        ],
        [
            "id" => 2,
            "likes" => ["-", 2], // resta
            "title" => "Nuevo título",
        ],
        [
            "id" => 3,
            "likes" => ["+", 5], // suma
        ],
        [
            "id" => 4,
            "likes" => ["*", 2], // multiplica
        ],
    ];

    $index = "id";

    /** @var Mavinoo\Batch\Batch $batch */
    $batch = batch();
    return $batch->update($postInstance, $toUpdate, $index);
});








