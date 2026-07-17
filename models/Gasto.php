<?php
class Gasto {
    private $conn;
    private $table_name = "gastos";

    public $id;
    public $descripcion;
    public $monto;
    public $tipo_negocio;
    public $usuario_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Registrar un gasto/egreso manual (arriendo, servicios, insumos, etc.)
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . "
            (descripcion, monto, tipo_negocio, usuario_id)
            VALUES (:descripcion, :monto, :tipo_negocio, :usuario_id)";

        $stmt = $this->conn->prepare($query);

        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->tipo_negocio = htmlspecialchars(strip_tags($this->tipo_negocio));

        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':monto', $this->monto);
        $stmt->bindParam(':tipo_negocio', $this->tipo_negocio);
        $stmt->bindParam(':usuario_id', $this->usuario_id);

        return $stmt->execute();
    }

    // Trae los gastos agrupados por mes (para el resumen financiero)
    public function totalesPorMes($meses_atras = 12) {
        $query = "SELECT TO_CHAR(fecha_gasto, 'YYYY-MM') as mes_key,
                          COALESCE(SUM(monto), 0) as total
                   FROM " . $this->table_name . "
                   WHERE fecha_gasto >= (CURRENT_DATE - (:meses || ' months')::interval)
                   GROUP BY mes_key";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':meses', $meses_atras);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // ['2026-07' => 150000, ...]
    }

    // Últimos gastos registrados (para mostrarlos en una lista)
    public function ultimos($cantidad = 10) {
        $query = "SELECT g.*, u.nombre as usuario_nombre
                   FROM " . $this->table_name . " g
                   LEFT JOIN usuarios u ON g.usuario_id = u.id
                   ORDER BY g.fecha_gasto DESC
                   LIMIT :cantidad";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':cantidad', (int)$cantidad, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
