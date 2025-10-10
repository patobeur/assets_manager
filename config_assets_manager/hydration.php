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
                ['Jean', 'Dupont', 'HYDRATION-S-001'],
                ['Marie', 'Curie', 'HYDRATION-S-002'],
                ['Pierre', 'Martin', 'HYDRATION-S-003'],
                ['Sophie', 'Bernard', 'HYDRATION-S-004'],
                ['Lucas', 'Robert', 'HYDRATION-S-005'],
                ['Camille', 'Richard', 'HYDRATION-S-006'],
                ['Léa', 'Petit', 'HYDRATION-S-007'],
                ['Louis', 'Durand', 'HYDRATION-S-008'],
                ['Chloé', 'Leroy', 'HYDRATION-S-009'],
                ['Gabriel', 'Moreau', 'HYDRATION-S-010'],
                ['Manon', 'Simon', 'HYDRATION-S-011'],
                ['Hugo', 'Laurent', 'HYDRATION-S-012'],
                ['Emma', 'Lefebvre', 'HYDRATION-S-013'],
                ['Adam', 'Roux', 'HYDRATION-S-014'],
                ['Juliette', 'Fournier', 'HYDRATION-S-015'],
            ];

            $student_ids = [];
            $stmt = $this->pdo->prepare("INSERT INTO am_students (first_name, last_name, barcode) VALUES (?, ?, ?)");
            foreach ($students as $student) {
                $stmt->execute($student);
                $student_ids[] = $this->pdo->lastInsertId();
            }

            // Data for materials
            $materials = [
                ['Ordinateur Portable Dell', 'Modèle Latitude 7420', 'available', 'HYDRATION-M-001'],
                ['Microscope Optique', 'Grossissement 1000x', 'available', 'HYDRATION-M-002'],
                ['Livre "Le Petit Prince"', 'Antoine de Saint-Exupéry', 'available', 'HYDRATION-M-003'],
                ['Calculatrice TI-83', 'Texas Instruments', 'available', 'HYDRATION-M-004'],
                ['Tablette iPad Air', 'Apple, 256GB', 'available', 'HYDRATION-M-005'],
                ['Kit de Chimie', '50 pièces', 'available', 'HYDRATION-M-006'],
                ['Appareil Photo Canon', 'EOS Rebel T7', 'available', 'HYDRATION-M-007'],
                ['Ballon de Basket', 'Taille 7', 'available', 'HYDRATION-M-008'],
                ['Guitare Acoustique', 'Yamaha F310', 'available', 'HYDRATION-M-009'],
                ['VidéoProjecteur Epson', 'HD 1080p', 'available', 'HYDRATION-M-010'],
                ['Oscilloscope Numérique', 'Tektronix', 'available', 'HYDRATION-M-011'],
                ['Mannequin de Secourisme', 'RCP', 'available', 'HYDRATION-M-012'],
                ['Dictionnaire Larousse', 'Édition 2023', 'available', 'HYDRATION-M-013'],
                ['Globe Terrestre', '30cm diamètre', 'available', 'HYDRATION-M-014'],
                ['Ensemble de Pinceaux', 'Peinture à l\'huile', 'available', 'HYDRATION-M-015'],
            ];

            $material_ids = [];
            $stmt = $this->pdo->prepare("INSERT INTO am_materials (name, description, status, barcode) VALUES (?, ?, ?, ?)");
            foreach ($materials as $material) {
                $stmt->execute($material);
                $material_ids[] = $this->pdo->lastInsertId();
            }

            // Data for loans
            $admin_user_id = $_SESSION['user_id'] ?? 1; // Default to 1 if not in session
            $loan_stmt = $this->pdo->prepare("INSERT INTO am_loans (student_id, material_id, loan_date, return_date, loan_user_id, return_user_id) VALUES (?, ?, ?, ?, ?, ?)");
            $update_material_status_stmt = $this->pdo->prepare("UPDATE am_materials SET status = ? WHERE id = ?");

            for ($i = 0; $i < 15; $i++) {
                $time = $i * 10;
                $loan_date = date('Y-m-d H:i:s', strtotime("-$time days"));
                $is_returned = $i >= 5; // First 5 are active, the rest are returned

                $return_date = null;
                $return_user_id = null;
                $material_status = 'loaned';

                if ($is_returned) {
                    $return_date = date('Y-m-d H:i:s', strtotime("-$time days + 5 hours"));
                    $return_user_id = $admin_user_id;
                    $material_status = 'available';
                }

                $update_material_status_stmt->execute([$material_status, $material_ids[$i]]);
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
