<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    // api get
    $app->get('/category', function(Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('SELECT * FROM category');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        if (count($results) > 0) {
            $response->getBody()->write(json_encode($results));
        } else {
            $response->getBody()->write(json_encode(['message' => 'Tidak dapat mengambil data category']));
        }
        return $response->withHeader('Content-Type','application/json');
    });

    // get data by id
    $app->get('/category/{id}', function(Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);

        $query = $db->prepare('SELECT * FROM category WHERE id_category=?');
        $query->execute([$args['id']]);
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        if (count($results) > 0) {
            $response->getBody()->write(json_encode($results[0]));
        } else {
            $response->getBody()->write(json_encode(['message' => 'Data tidak ditemukan']));
        }

        return $response->withHeader('Content-Type', 'application/json');
});

    //post data
    $app->post('/category', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);
    
        // Ambil data dari request
        $data = $request->getParsedBody();
        $categoryName = $data['category_name'];
    
        // Panggil stored procedure untuk menyimpan data
        $query = $db->prepare('CALL create_category(?, @result)');
        $query->bindParam(1, $categoryName, PDO::PARAM_STR);
        $query->execute();
    
        // Ambil hasil dari variabel MySQL @result
        $resultQuery = $db->query('SELECT @result as result');
        $result = $resultQuery->fetch(PDO::FETCH_ASSOC)['result'];
    
        // Tangani hasil operasi penyimpanan
        if (strpos($result, 'Error') !== false) {
            $response->getBody()->write(json_encode(['message' => $result]));
            $response = $response->withStatus(500);
        } else {
            $response->getBody()->write(json_encode(['message' => $result]));
        }
    
        return $response->withHeader('Content-Type', 'application/json');
    });


    // Update
    $app->put('/category/{id}', function (Request $request, Response $response, $args) {
        try {
            $db = $this->get(PDO::class);
            $categoryId = $args['id'];
            $data = $request->getParsedBody();
            $newCategoryName = $data['new_category_name'];

            // Prepare the call to the MySQL stored procedure
            $query = $db->prepare('CALL update_category(?,?,@result)');
            $query->bindParam(1, $categoryId, PDO::PARAM_INT);
            $query->bindParam(2, $newCategoryName, PDO::PARAM_STR);
            $query->execute();

            // Fetch the result from the stored procedure
            $resultQuery = $db->query('SELECT @result as result');
            $result = $resultQuery->fetch(PDO::FETCH_ASSOC);

            // Check the result and respond accordingly
            if ($result['result'] === 'Category berhasil diperbarui.') {
                $response->getBody()->write(json_encode(['message' => $result['result']]));
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write(json_encode(['error' => $result['result']]));
                return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        } catch (Exception $e) {
            return $response->withStatus(500)->getBody()->write(json_encode(['error' => 'Terjadi kesalahan saat memperbarui kategori']));
        }
    });


    // Delete
    $app->delete('/category/{id}', function (Request $request, Response $response, $args) {
        try {
            $currentId = $args['id'];

            $db = $this->get(PDO::class);
            $query = $db->prepare('CALL delete_category(?, @result)');
            $query->bindParam(1, $currentId, PDO::PARAM_INT);
            $query->execute();

            $resultQuery = $db->query("SELECT @result as result");
            $result = $resultQuery->fetch(PDO::FETCH_ASSOC)['result'];

            if ($result === 'Category berhasil dihapus.') {
                $response->getBody()->write(json_encode(['message' => 'Category berhasil dihapus']));
            } else {
                $response->getBody()->write(json_encode(['message' => 'Gagal menghapus category']));
                $response = $response->withStatus(500);
            }

            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->getBody()->write(json_encode(['error' => 'Terjadi kesalahan saat menghapus kategori']));
        }
    });

    $app->get('/country', function (Request $request, Response $response) {
        try {
            $db = $this->get(PDO::class);

            $query = $db->query('CALL get_country()');
            $results = $query->fetchAll(PDO::FETCH_ASSOC);

            if (count($results) > 0) {
                $response->getBody()->write(json_encode($results));
            } else {
                $response->getBody()->write(json_encode(['message' => 'Tidak dapat mengambil data country']));
            }

            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->getBody()->write(json_encode(['error' => 'Terjadi kesalahan saat mengambil data country']));
        }
    });

    $app->post('/country', function (Request $request, Response $response) {
        try {
            $db = $this->get(PDO::class);

            $data = $request->getParsedBody();
            $countryName = $data['country_name'];

            $query = $db->prepare('CALL create_country_mobil(?, @result)');
            $query->bindParam(1, $countryName, PDO::PARAM_STR);
            $query->execute();

            $resultQuery = $db->query("SELECT @result as result");
            $result = $resultQuery->fetch(PDO::FETCH_ASSOC)['result'];

            if ($result === 'Country berhasil dibuat.') {
                $response->getBody()->write(json_encode(['message' => $result]));
            } else {
                $response->getBody()->write(json_encode(['error' => $result]));
                $response = $response->withStatus(500);
            }

            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->getBody()->write(json_encode(['error' => 'Terjadi kesalahan saat membuat data country']));
        }
    });

    $app->put('/country/{id}', function (Request $request, Response $response, array $args) {
        try {
            $db = $this->get(PDO::class);
            $parseBody = $request->getParsedBody();
            
            $currentId = $args['id'];
            $newCountryName = $parseBody['new_country_name'];

            $query = $db->prepare('CALL update_country_mobil(?,?,@result)');
            $query->execute([$currentId, $newCountryName]);
            
            $resultQuery = $db->query("SELECT @result as result");
            $result = $resultQuery->fetch(PDO::FETCH_ASSOC)['result'];

            if ($result === 'Country mobil berhasil diperbarui.') {
                $response->getBody()->write(json_encode(['message' => $result]));
            } else {
                $response->getBody()->write(json_encode(['message' => 'Gagal memperbarui data country mobil']));
                $response = $response->withStatus(500);
            }

            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->getBody()->write(json_encode(['error' => 'Terjadi kesalahan saat memperbarui data country mobil']));
        }
    });

    $app->delete('/country/{id}', function (Request $request, Response $response, array $args) {
        try {
            $countryId = $args['id'];
            $db = $this->get(PDO::class);

            $query = $db->prepare('CALL delete_country_mobil(?, @result)');
            $query->execute([$countryId]);
            
            $selectResult = $db->query("SELECT @result as result");
            $result = $selectResult->fetch(PDO::FETCH_ASSOC)['result'];

            if (strpos($result, 'Error') !== false) {
                return $response->withStatus(500)->getBody()->write(json_encode(['error' => $result]));
            }

            $response->getBody()->write(json_encode(['message' => $result]));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->getBody()->write(json_encode(['error' => 'Terjadi kesalahan saat menghapus country mobil']));
        }
    });

    $app->get('/merk-mobil', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL get_merk_mobil()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        if (count($results) > 0) {
            $response->getBody()->write(json_encode($results));
        } else {
            $response->getBody()->write(json_encode(['message' => 'Tidak dapat mengambil data merk mobil']));
        }

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/merk-mobil', function (Request $request, Response $response) {
        try {
            $db = $this->get(PDO::class);

            $data = $request->getParsedBody();
            $merkName = $data['merk_name'];

            $query = $db->prepare('CALL create_merk_mobil(?, @result)');
            $query->execute([$merkName]);

            $selectResult = $db->query("SELECT @result as result");
            $result = $selectResult->fetch(PDO::FETCH_ASSOC)['result'];

            if (strpos($result, 'Error') !== false) {
                return $response->withStatus(500)->getBody()->write(json_encode(['error' => $result]));
            }

            $response->getBody()->write(json_encode(['message' => $result]));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->getBody()->write(json_encode(['error' => 'Terjadi kesalahan saat membuat merk mobil']));
        }
    });

    $app->put('/merk-mobil/{id}', function (Request $request, Response $response, array $args) {
        try {
            $merkId = $args['id'];
            $db = $this->get(PDO::class);

            $data = $request->getParsedBody();
            $newMerkName = $data['new_merk_name'];

            $query = $db->prepare('CALL update_merk_mobil(?, ?, @result)');
            $query->execute([$merkId, $newMerkName]);

            $selectResult = $db->query("SELECT @result as result");
            $result = $selectResult->fetch(PDO::FETCH_ASSOC)['result'];

            if (strpos($result, 'Error') !== false) {
                return $response->withStatus(500)->getBody()->write(json_encode(['error' => $result]));
            }

            $response->getBody()->write(json_encode(['message' => $result]));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->getBody()->write(json_encode(['error' => 'Terjadi kesalahan saat memperbarui merk mobil']));
        }
    });

    $app->delete('/merk-mobil/{id}', function (Request $request, Response $response, array $args) {
        try {
            $merkId = $args['id'];
            $db = $this->get(PDO::class);

            $query = $db->prepare('CALL delete_merk_mobil(?, @result)');
            $query->execute([$merkId]);
            
            $selectResult = $db->query("SELECT @result as result");
            $result = $selectResult->fetch(PDO::FETCH_ASSOC)['result'];

            if (strpos($result, 'Error') !== false) {
                return $response->withStatus(500)->getBody()->write(json_encode(['error' => $result]));
            }

            $response->getBody()->write(json_encode(['message' => $result]));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->getBody()->write(json_encode(['error' => 'Terjadi kesalahan saat menghapus merk mobil']));
        }
    });

    $app->get('/type-mobil', function (Request $request, Response $response) {
        try {
            $db = $this->get(PDO::class);

            $query = $db->prepare('CALL get_type_mobil()');
            $query->execute();

            $results = $query->fetchAll(PDO::FETCH_ASSOC);

            if (count($results) > 0) {
                $response->getBody()->write(json_encode($results));
            } else {
                $response->getBody()->write(json_encode(['message' => 'Tidak dapat mengambil data type mobil']));
            }

            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->getBody()->write(json_encode(['error' => 'Terjadi kesalahan saat mengambil data type mobil']));
        }
    });

    $app->post('/type-mobil', function (Request $request, Response $response) {
        try {
            $data = $request->getParsedBody();
            $idMobil = $data['id_mobil'];
            $namaTypeMobil = $data['nama_type_mobil'];
            $gvwKg = $data['GVW_Kg'];
            $gearRatio = $data['Gear_Ratio'];
            $speed = $data['Speed'];
            $rakitan = $data['Rakitan'];
            $idCategory = $data['id_category'];
            $idCountry = $data['id_country'];
            $cc = $data['cc'];

            $db = $this->get(PDO::class);
            $query = $db->prepare('CALL create_type_mobil(?, ?, ?, ?, ?, ?, ?, ?, ?, @result)');
            $query->execute([$idMobil, $namaTypeMobil, $gvwKg, $gearRatio, $speed, $rakitan, $idCategory, $idCountry, $cc]);

            $selectResult = $db->query("SELECT @result as result");
            $result = $selectResult->fetch(PDO::FETCH_ASSOC)['result'];

            if (strpos($result, 'Error') !== false) {
                return $response->withStatus(500)->getBody()->write(json_encode(['error' => $result]));
            }

            $response->getBody()->write(json_encode(['message' => $result]));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->getBody()->write(json_encode(['error' => 'Terjadi kesalahan saat membuat type mobil']));
        }
    });

    $app->put('/type-mobil/{id}', function (Request $request, Response $response, array $args) {
        try {
            $typeId = $args['id'];
            $db = $this->get(PDO::class);

            $data = $request->getParsedBody();
            $newIdMobil = $data['new_id_mobil'];
            $newNamaTypeMobil = $data['new_nama_type_mobil'];
            $newGVWKg = $data['new_GVW_Kg'];
            $newGearRatio = $data['new_Gear_Ratio'];
            $newSpeed = $data['new_Speed'];
            $newRakitan = $data['new_Rakitan'];
            $newIdCategory = $data['new_id_category'];
            $newIdCountry = $data['new_id_country'];
            $newCc = $data['new_cc'];

            $query = $db->prepare('CALL update_type_mobil(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @result)');
            $query->execute([$typeId, $newIdMobil, $newNamaTypeMobil, $newGVWKg, $newGearRatio, $newSpeed, $newRakitan, $newIdCategory, $newIdCountry, $newCc]);

            $selectResult = $db->query("SELECT @result as result");
            $result = $selectResult->fetch(PDO::FETCH_ASSOC)['result'];

            if (strpos($result, 'Error') !== false) {
                return $response->withStatus(500)->getBody()->write(json_encode(['error' => $result]));
            }

            $response->getBody()->write(json_encode(['message' => $result]));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->getBody()->write(json_encode(['error' => 'Terjadi kesalahan saat memperbarui tipe mobil']));
        }
    });

    $app->delete('/type-mobil/{id}', function (Request $request, Response $response, array $args) {
        try {
            $typeId = $args['id'];
            $db = $this->get(PDO::class);

            $query = $db->prepare('CALL delete_type_mobil(?, @result)');
            $query->execute([$typeId]);

            $selectResult = $db->query("SELECT @result as result");
            $result = $selectResult->fetch(PDO::FETCH_ASSOC)['result'];

            if (strpos($result, 'Error') !== false) {
                return $response->withStatus(500)->getBody()->write(json_encode(['error' => $result]));
            }

            $response->getBody()->write(json_encode(['message' => $result]));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500)->getBody()->write(json_encode(['error' => 'Terjadi kesalahan saat menghapus type mobil']));
        }
    });

};
