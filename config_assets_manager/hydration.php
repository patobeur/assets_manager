<?php

class Hydration
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function populateTables()
    {
        $this->pdo->beginTransaction();

        try {
            // Data for students
            $students = [
                ['Aiden', 'Walker', 'HYDRATION-S-001', 'aiden.walker@patobeur.pat', 1, 1, 1],
                ['Isabella', 'Moreno', 'HYDRATION-S-002', 'isabella.moreno@patobeur.pat', 1, 1, 1],
                ['Luca', 'Bianchi', 'HYDRATION-S-003', 'luca.bianchi@patobeur.pat', 1, 1, 1],
                ['Nora', 'Andersson', 'HYDRATION-S-004', 'nora.andersson@patobeur.pat', 1, 1, 1],
                ['Mateo', 'Silva', 'HYDRATION-S-005', 'mateo.silva@patobeur.pat', 1, 1, 1],
                ['Elena', 'Kuznetsova', 'HYDRATION-S-006', 'elena.kuznetsova@patobeur.pat', 1, 1, 1],
                ['Kai', 'Tanaka', 'HYDRATION-S-007', 'kai.tanaka@patobeur.pat', 1, 1, 1],
                ['Sofia', 'Garcia', 'HYDRATION-S-008', 'sofia.garcia@patobeur.pat', 1, 1, 0],
                ['Noah', 'Johnson', 'HYDRATION-S-009', 'noah.johnson@patobeur.pat', 1, 1, 1],
                ['Mila', 'Novak', 'HYDRATION-S-010', 'mila.novak@patobeur.pat', 1, 1, 1],
                ['Ethan', 'Carter', 'HYDRATION-S-011', 'ethan.carter@patobeur.pat', 1, 1, 1],
                ['Aria', 'Romero', 'HYDRATION-S-012', 'aria.romero@patobeur.pat', 1, 1, 1],
                ['Leo', 'Petrov', 'HYDRATION-S-013', 'leo.petrov@patobeur.pat', 1, 1, 1],
                ['Amara', 'Singh', 'HYDRATION-S-014', 'amara.singh@patobeur.pat', 1, 1, 1],
                ['Ravi', 'Patel', 'HYDRATION-S-015', 'ravi.patel@patobeur.pat', 1, 1, 1],
            ];

            $student_ids = [];
            $stmt = $this->pdo->prepare("INSERT INTO am_students (first_name, last_name, barcode, email, section_id, promo_id, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($students as $student) {
                $stmt->execute($student);
                $student_ids[] = $this->pdo->lastInsertId();
            }

            // Data for materials
            $materials = [
                ['Ordinateur Portable Dell', 'Modèle Latitude 7420', 1, 'HYDRATION-M-001', 1],
                ['Microscope Optique', 'Grossissement 1000x', 1, 'HYDRATION-M-002', 1],
                ['Livre "Le Petit Prince"', 'Antoine de Saint-Exupéry', 1, 'HYDRATION-M-003', 1],
                ['Calculatrice TI-83', 'Texas Instruments', 1, 'HYDRATION-M-004', 1],
                ['Tablette iPad Air', 'Apple, 256GB', 1, 'HYDRATION-M-005', 1],
                ['Kit de Chimie', '50 pièces', 1, 'HYDRATION-M-006', 1],
                ['Appareil Photo Canon', 'EOS Rebel T7', 1, 'HYDRATION-M-007', 1],
                ['Ballon de Basket', 'Taille 7', 1, 'HYDRATION-M-008', 1],
                ['Guitare Acoustique', 'Yamaha F310', 1, 'HYDRATION-M-009', 1],
                ['VidéoProjecteur Epson', 'HD 1080p', 1, 'HYDRATION-M-010', 1],
                ['Oscilloscope Numérique', 'Tektronix', 1, 'HYDRATION-M-011', 1],
                ['Mannequin de Secourisme', 'RCP', 1, 'HYDRATION-M-012', 1],
                ['Dictionnaire Larousse', 'Édition 2023', 1, 'HYDRATION-M-013', 1],
                ['Globe Terrestre', '30cm diamètre', 1, 'HYDRATION-M-014', 1],
                ['Ensemble de Pinceaux', 'Peinture à l\'huile', 1, 'HYDRATION-M-015', 1]
            ];

            $material_ids = [];
            $stmt = $this->pdo->prepare("INSERT INTO am_materials (name, description, material_status_id, barcode, material_categories_id) VALUES (?, ?, ?, ?, ?)");
            foreach ($materials as $material) {
                $stmt->execute($material);
                $material_ids[] = $this->pdo->lastInsertId();
            }

            // Data for loans
            $admin_user_id = $_SESSION['user_id'] ?? 1; // Default to 1 if not in session
            $loan_stmt = $this->pdo->prepare("INSERT INTO am_loans (student_id, material_id, loan_date, return_date, loan_user_id, return_user_id) VALUES (?, ?, ?, ?, ?, ?)");
            $update_material_status_stmt = $this->pdo->prepare("UPDATE am_materials SET material_status_id = ? WHERE id = ?");

            for ($i = 0; $i < 15; $i++) {
                $time = $i * 10;
                $loan_date = date('Y-m-d H:i:s', strtotime("-$time days"));
                $is_returned = $i >= 5; // First 5 are active, the rest are returned

                $return_date = null;
                $return_user_id = null;
                $material_status_id = 2;

                if ($is_returned) {
                    $return_date = date('Y-m-d H:i:s', strtotime("-$time days + 5 hours"));
                    $return_user_id = $admin_user_id;
                    $material_status_id = 1;
                }

                $update_material_status_stmt->execute([$material_status_id, $material_ids[$i]]);
                $loan_stmt->execute([$student_ids[$i], $material_ids[$i], $loan_date, $return_date, $admin_user_id, $return_user_id]);
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function clearTables()
    {
        $this->pdo->beginTransaction();
        try {
            // Get student IDs to delete from loans
            $stmt = $this->pdo->prepare("SELECT id FROM am_students WHERE barcode LIKE 'HYDRATION-%'");
            $stmt->execute();
            $student_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($student_ids)) {
                // Delete loans associated with the hydrated students
                $in_placeholders = implode(',', array_fill(0, count($student_ids), '?'));
                $loan_delete_stmt = $this->pdo->prepare("DELETE FROM am_loans WHERE student_id IN ($in_placeholders)");
                $loan_delete_stmt->execute($student_ids);
            }

            // Delete materials
            $material_stmt = $this->pdo->prepare("DELETE FROM am_materials WHERE barcode LIKE 'HYDRATION-%'");
            $material_stmt->execute();

            // Delete students
            $student_stmt = $this->pdo->prepare("DELETE FROM am_students WHERE barcode LIKE 'HYDRATION-%'");
            $student_stmt->execute();

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
