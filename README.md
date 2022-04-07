# tc-api-new

## Setup

Install dependencies:

    * `composer install --prefer-dist`

Update git hooks:

    * `composer cghooks update`


## Swagger Documentation

 - Extend RestfulController class
 - Inside each of controllers, you should have the appropriate annotations above each public method
```
    /**
     * @OA\Get(
     *     path="/sample/{category}/things",
     *     operationId="/sample/category/things",
     *     tags={"yourtag"},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="The category parameter in path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="criteria",
     *         in="query",
     *         description="Some optional other parameter",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns some sample category things",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function getThings(Request $request, $category)
    {
        $criteria= $request->input("criteria");
        if (! isset($category)) {
            return response()->json(null, Response::HTTP_BAD_REQUEST);
        }

        // ...

        return response()->json(["thing1", "thing2"], Response::HTTP_OK);
    }
```
- Generate swagger documentation using the command.
`php artisan swagger-lume:generate`
- Run this each time you update the documentation
- Swagger UI: `/api/documentation`

## Pull Requests

1. Describe how to test the PR: urls, environment variables and other needs.
2. Refer to issue(s)/card(s) the PR solves.
3. Refer back to the PR on card(s).
4. Merge the target branch into the PR branch. Fix any conflicts that might appear.
5. Add screenshots of the new behavior.
6. Add a description including the context and the chosen implementation strategy.
7. Make sure the code follows the code guidelines.
8. Make sure all items in the template are filled in either with the relevant info or N/A

## Code Guidelines: General

1. Adhere to [PSR-12](https://www.php-fig.org/psr/psr-12/)
2. Code smell: make sure you would understand your code if you read it a few months from now.
3. DRY: Don't Repeat Yourself.
4. [SOLID](https://en.wikipedia.org/wiki/SOLID)
6. Write tests for your feature.
7. Code smell: code should be self-explanatory as much as possible. Otherwise add comments to your code. 
8. Use dependency injection as much as possible.
9. Put configuration variables in `.env`. Do not hard code URIs.

## Code Guidelines: APIs

1. CRUD controllers: 1 controller per model / entity
2. Use route name format: `api/entity-name`
2. Use the following controller method names for CRUD operations
    * List/search: `index()` 
    * Read (single object): `show()`
    * Create: `create()`
    * Update: `update()`
    * Delete: `destroy()`
3. Use the mime type `application/json` for requests
4. Put search parameters, page limits, offsets, sort specs in query string (GET)
5. Put object parameters in the request body as JSON
6. Pass a valid `access-token` header on all APIs.
7. Add swagger annotations to your controller methods that are exposed as APIs. 
8. Put DMS resources under the DMS routes. Look for the `dms` group in `routes/api.php`.

## Code Guidelines: Architecture

1. Avoid fat controllers. 
    * Avoid queries in controllers.
    * Minimize logic
2. Define entities in `Models`.
3. Define processes/business logic in `Services`. This way you can reuse code in controllers, jobs, events, etc. 
4. Define asynchronous tasks/long-running tasks in `Jobs`. Even better, in `Services` wrapped in `Jobs`. 
5. Define data operations in `Repositories`. `Repositories` may contain more than just CRUD operations.

## Tests

1. Put unit tests under `tests/Unit` and feature tests under `tests/Feature`. 
2. (Directory structures under `Unit` and `Feature` TBD).
3. (Coverage of code to test TBD).
4. The namespace root for tests is `Tests` and is located in the `tests/` directory.
5. Feature test classes should extend `Tests\TestCase`.
6. A Unit test class can extend `PHPUnit\Framework\TestCase` if it does not need the Application instance.
7. Run tests by running `./vendor/bin/phpunit` (all), `./vendor/bin/phpunit --testsuite Unit` (unit tests only), `./vendor/bin/phpunit --testsuite Feature` (feature tests only). Another way to run test is by using `composer test` command. You can add flag like so `composer test -- --filter=RemoveDeletedModelFromESIndexActionTest` 


## Git hooks

We use [composer-git-hooks](https://github.com/BrainMaestro/composer-git-hooks#composer-git-hooks) to setup and run the following git hooks:

* `pre-commit`
    * `l5-swagger:generate` - check if swagger documentation doesn't have syntax errors
