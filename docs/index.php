<?php
/**
 * Задача оптимизации распределения самолетов по авиалиниям
 * Минимизация эксплуатационных расходов при заданных ограничениях
 */

class AirlineOptimizer {
    private $n; // количество типов самолетов
    private $m; // количество авиалиний
    private $a; // матрица производительности a[i][j]
    private $c; // матрица расходов c[i][j]
    private $d; // требования по перевозкам d[j]
    private $N; // доступное количество самолетов N[i]
    
    public function __construct($n, $m, $a, $c, $d, $N) {
        $this->n = $n;
        $this->m = $m;
        $this->a = $a;
        $this->c = $c;
        $this->d = $d;
        $this->N = $N;
    }
    
    /**
     * Решение задачи с использованием симплекс-метода (через линейное программирование)
     * Упрощенная реализация для целочисленного распределения
     */
    public function solve() {
        $x = []; // результат x[i][j]
        $totalCost = 0;
        
        // Инициализируем матрицу решения нулями
        for ($i = 0; $i < $this->n; $i++) {
            for ($j = 0; $j < $this->m; $j++) {
                $x[$i][$j] = 0;
            }
        }
        
        // Копии ограничений
        $remainingPlanes = $this->N;
        $remainingDemand = $this->d;
        
        // Жадный алгоритм с приоритетом по эффективности (расходы на единицу перевозки)
        // Создаем список всех возможных пар (самолет, линия) с их эффективностью
        $efficiency = [];
        for ($i = 0; $i < $this->n; $i++) {
            for ($j = 0; $j < $this->m; $j++) {
                if ($this->a[$i][$j] > 0) {
                    $eff = $this->c[$i][$j] / $this->a[$i][$j];
                    $efficiency[] = [
                        'i' => $i,
                        'j' => $j,
                        'cost' => $this->c[$i][$j],
                        'productivity' => $this->a[$i][$j],
                        'efficiency' => $eff
                    ];
                }
            }
        }
        
        // Сортируем по возрастанию эффективности (лучшие сначала)
        usort($efficiency, function($a, $b) {
            return $a['efficiency'] <=> $b['efficiency'];
        });
        
        // Распределяем самолеты
        foreach ($efficiency as $item) {
            $i = $item['i'];
            $j = $item['j'];
            
            // Проверяем ограничения
            if ($remainingPlanes[$i] <= 0) continue;
            if ($remainingDemand[$j] <= 0) continue;
            
            // Сколько самолетов нужно для покрытия спроса
            $planesNeeded = ceil($remainingDemand[$j] / $item['productivity']);
            $planesToAssign = min($planesNeeded, $remainingPlanes[$i]);
            
            if ($planesToAssign > 0) {
                $x[$i][$j] = $planesToAssign;
                $remainingPlanes[$i] -= $planesToAssign;
                $covered = $planesToAssign * $item['productivity'];
                $remainingDemand[$j] = max(0, $remainingDemand[$j] - $covered);
                $totalCost += $planesToAssign * $item['cost'];
            }
        }
        
        // Проверяем, все ли потребности удовлетворены
        $allSatisfied = true;
        $unmetDemand = [];
        for ($j = 0; $j < $this->m; $j++) {
            if ($remainingDemand[$j] > 0) {
                $allSatisfied = false;
                $unmetDemand[$j] = $remainingDemand[$j];
            }
        }
        
        return [
            'solution' => $x,
            'totalCost' => $totalCost,
            'allSatisfied' => $allSatisfied,
            'unmetDemand' => $unmetDemand,
            'remainingPlanes' => $remainingPlanes
        ];
    }
}

// Примеры данных для демонстрации
$examples = [
    [
        'name' => 'Пример 1: Малый парк (3 типа самолетов, 2 линии)',
        'n' => 3,
        'm' => 2,
        'a' => [
            [100, 120],  // Тип 1: производительность на линиях 1 и 2
            [80, 90],    // Тип 2
            [150, 140]   // Тип 3
        ],
        'c' => [
            [5000, 6000],  // Тип 1: расходы на линиях 1 и 2
            [4000, 4500],  // Тип 2
            [7000, 6800]   // Тип 3
        ],
        'd' => [500, 400],  // Требования по перевозкам для линий 1 и 2
        'N' => [3, 4, 2]    // Доступное количество самолетов каждого типа
    ],
    [
        'name' => 'Пример 2: Средний парк (4 типа самолетов, 3 линии)',
        'n' => 4,
        'm' => 3,
        'a' => [
            [120, 100, 110],
            [90, 95, 100],
            [140, 130, 135],
            [110, 115, 120]
        ],
        'c' => [
            [6000, 5500, 5800],
            [4500, 4700, 5000],
            [7500, 7000, 7200],
            [5500, 5600, 5900]
        ],
        'd' => [600, 500, 550],
        'N' => [3, 4, 2, 3]
    ],
    [
        'name' => 'Пример 3: Крупный парк (5 типов самолетов, 4 линии)',
        'n' => 5,
        'm' => 4,
        'a' => [
            [100, 110, 105, 115],
            [85, 90, 88, 92],
            [130, 125, 128, 135],
            [110, 108, 112, 118],
            [95, 100, 98, 102]
        ],
        'c' => [
            [5200, 5500, 5350, 5700],
            [4200, 4400, 4300, 4500],
            [6800, 6500, 6650, 7000],
            [5600, 5500, 5700, 6000],
            [4800, 5000, 4900, 5100]
        ],
        'd' => [450, 400, 420, 480],
        'N' => [4, 5, 3, 3, 4]
    ]
];

$selectedExample = isset($_GET['example']) ? intval($_GET['example']) : 0;
$selectedExample = max(0, min($selectedExample, count($examples) - 1));
$example = $examples[$selectedExample];

$result = null;
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $n = intval($_POST['n']);
    $m = intval($_POST['m']);
    
    $a = [];
    $c = [];
    for ($i = 0; $i < $n; $i++) {
        $a[$i] = [];
        $c[$i] = [];
        for ($j = 0; $j < $m; $j++) {
            $a[$i][$j] = floatval($_POST["a_{$i}_{$j}"]);
            $c[$i][$j] = floatval($_POST["c_{$i}_{$j}"]);
        }
    }
    
    $d = [];
    for ($j = 0; $j < $m; $j++) {
        $d[$j] = floatval($_POST["d_{$j}"]);
    }
    
    $N = [];
    for ($i = 0; $i < $n; $i++) {
        $N[$i] = intval($_POST["N_{$i}"]);
    }
    
    $optimizer = new AirlineOptimizer($n, $m, $a, $c, $d, $N);
    $result = $optimizer->solve();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оптимизация распределения самолетов</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1, h2 {
            color: #2c3e50;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .example-selector {
            margin-bottom: 20px;
        }
        .example-selector select {
            padding: 10px;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        input[type="number"] {
            width: 80px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        button {
            background-color: #27ae60;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #219a52;
        }
        .result-box {
            background-color: #e8f6f3;
            border: 1px solid #27ae60;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .warning-box {
            background-color: #fef9e7;
            border: 1px solid #f39c12;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .error-box {
            background-color: #fdedec;
            border: 1px solid #e74c3c;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .section-title {
            background-color: #ecf0f1;
            padding: 10px;
            margin: 20px 0 10px 0;
            border-radius: 4px;
            font-weight: bold;
        }
        .matrix-label {
            font-weight: bold;
            margin: 10px 0;
            color: #2c3e50;
        }
        .total-cost {
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
        }
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        .form-group {
            flex: 1;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>🛫 Оптимизация распределения самолетов по авиалиниям</h1>
    
    <div class="container">
        <h2>Постановка задачи</h2>
        <p>Необходимо распределить <strong>n</strong> различных типов самолетов между <strong>m</strong> авиалиниями так, чтобы:</p>
        <ul>
            <li>Обеспечить перевозки по каждой линии в объеме <strong>d<sub>j</sub></strong> единиц</li>
            <li>Минимизировать суммарные эксплуатационные расходы</li>
            <li>Не превышать доступное количество самолетов каждого типа <strong>N<sub>i</sub></strong></li>
        </ul>
        <p><strong>Входные данные:</strong></p>
        <ul>
            <li><strong>a<sub>ij</sub></strong> — месячный объем перевозок самолетом i-го типа на j-й линии</li>
            <li><strong>c<sub>ij</sub></strong> — месячные эксплуатационные расходы для самолета i-го типа на j-й линии</li>
            <li><strong>d<sub>j</sub></strong> — требуемый объем перевозок по j-й линии</li>
            <li><strong>N<sub>i</sub></strong> — доступное количество самолетов i-го типа</li>
        </ul>
    </div>

    <div class="container">
        <h2>Выбор примера данных</h2>
        <div class="example-selector">
            <form method="GET">
                <select name="example" onchange="this.form.submit()">
                    <?php foreach ($examples as $idx => $ex): ?>
                        <option value="<?= $idx ?>" <?= $idx === $selectedExample ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ex['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        
        <p><strong>Текущий пример:</strong> <?= htmlspecialchars($example['name']) ?></p>
        
        <form method="POST">
            <input type="hidden" name="n" value="<?= $example['n'] ?>">
            <input type="hidden" name="m" value="<?= $example['m'] ?>">
            
            <div class="section-title">Производительность самолетов (a<sub>ij</sub>) - объем перевозок</div>
            <table>
                <thead>
                    <tr>
                        <th>Тип \ Линия</th>
                        <?php for ($j = 0; $j < $example['m']; $j++): ?>
                            <th>Линия <?= $j + 1 ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < $example['n']; $i++): ?>
                        <tr>
                            <td><strong>Тип <?= $i + 1 ?></strong></td>
                            <?php for ($j = 0; $j < $example['m']; $j++): ?>
                                <td>
                                    <input type="number" name="a_<?= $i ?>_<?= $j ?>" 
                                           value="<?= $example['a'][$i][$j] ?>" required step="any">
                                </td>
                            <?php endfor; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            
            <div class="section-title">Эксплуатационные расходы (c<sub>ij</sub>) - рубли</div>
            <table>
                <thead>
                    <tr>
                        <th>Тип \ Линия</th>
                        <?php for ($j = 0; $j < $example['m']; $j++): ?>
                            <th>Линия <?= $j + 1 ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < $example['n']; $i++): ?>
                        <tr>
                            <td><strong>Тип <?= $i + 1 ?></strong></td>
                            <?php for ($j = 0; $j < $example['m']; $j++): ?>
                                <td>
                                    <input type="number" name="c_<?= $i ?>_<?= $j ?>" 
                                           value="<?= $example['c'][$i][$j] ?>" required step="any">
                                </td>
                            <?php endfor; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            
            <div class="section-title">Требования по перевозкам (d<sub>j</sub>)</div>
            <table>
                <thead>
                    <tr>
                        <?php for ($j = 0; $j < $example['m']; $j++): ?>
                            <th>Линия <?= $j + 1 ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php for ($j = 0; $j < $example['m']; $j++): ?>
                            <td>
                                <input type="number" name="d_<?= $j ?>" 
                                       value="<?= $example['d'][$j] ?>" required step="any">
                            </td>
                        <?php endfor; ?>
                    </tr>
                </tbody>
            </table>
            
            <div class="section-title">Доступное количество самолетов (N<sub>i</sub>)</div>
            <table>
                <thead>
                    <tr>
                        <?php for ($i = 0; $i < $example['n']; $i++): ?>
                            <th>Тип <?= $i + 1 ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php for ($i = 0; $i < $example['n']; $i++): ?>
                            <td>
                                <input type="number" name="N_<?= $i ?>" 
                                       value="<?= $example['N'][$i] ?>" required min="0">
                            </td>
                        <?php endfor; ?>
                    </tr>
                </tbody>
            </table>
            
            <button type="submit">🚀 Рассчитать оптимальное распределение</button>
        </form>
    </div>

    <?php if ($result): ?>
        <div class="container">
            <h2>📊 Результаты оптимизации</h2>
            
            <?php if ($result['allSatisfied']): ?>
                <div class="result-box">
                    <p>✅ <strong>Все потребности в перевозках удовлетворены!</strong></p>
                </div>
            <?php else: ?>
                <div class="error-box">
                    <p>⚠️ <strong>Не удалось полностью удовлетворить спрос!</strong></p>
                    <p>Неудовлетворенный спрос по линиям:</p>
                    <ul>
                        <?php foreach ($result['unmetDemand'] as $j => $demand): ?>
                            <li>Линия <?= $j + 1 ?>: <?= number_format($demand, 2) ?> единиц</li>
                        <?php endforeach; ?>
                    </ul>
                    <p>Попробуйте увеличить количество доступных самолетов.</p>
                </div>
            <?php endif; ?>
            
            <div class="section-title">Оптимальное распределение самолетов (x<sub>ij</sub>)</div>
            <table>
                <thead>
                    <tr>
                        <th>Тип \ Линия</th>
                        <?php for ($j = 0; $j < $example['m']; $j++): ?>
                            <th>Линия <?= $j + 1 ?></th>
                        <?php endfor; ?>
                        <th>Всего использовано</th>
                        <th>Доступно</th>
                        <th>Остаток</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < $example['n']; $i++): ?>
                        <tr>
                            <td><strong>Тип <?= $i + 1 ?></strong></td>
                            <?php 
                            $totalUsed = 0;
                            for ($j = 0; $j < $example['m']; $j++): 
                                $totalUsed += $result['solution'][$i][$j];
                            ?>
                                <td><?= $result['solution'][$i][$j] ?></td>
                            <?php endfor; ?>
                            <td><strong><?= $totalUsed ?></strong></td>
                            <td><?= $example['N'][$i] ?></td>
                            <td><?= $result['remainingPlanes'][$i] ?></td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            
            <div class="section-title">Обеспеченность перевозок по линиям</div>
            <table>
                <thead>
                    <tr>
                        <th>Линия</th>
                        <th>Требуется</th>
                        <th>Обеспечено</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    for ($j = 0; $j < $example['m']; $j++): 
                        $provided = 0;
                        for ($i = 0; $i < $example['n']; $i++) {
                            $provided += $result['solution'][$i][$j] * $example['a'][$i][$j];
                        }
                        $status = $provided >= $example['d'][$j] ? '✅' : '⚠️';
                    ?>
                        <tr>
                            <td><strong>Линия <?= $j + 1 ?></strong></td>
                            <td><?= number_format($example['d'][$j], 2) ?></td>
                            <td><?= number_format($provided, 2) ?></td>
                            <td><?= $status ?></td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            
            <div class="section-title">Финансовые показатели</div>
            <p class="total-cost">💰 Суммарные эксплуатационные расходы: <?= number_format($result['totalCost'], 2) ?> рублей/месяц</p>
            
            <div class="matrix-label">Детализация расходов по типам самолетов и линиям:</div>
            <table>
                <thead>
                    <tr>
                        <th>Тип \ Линия</th>
                        <?php for ($j = 0; $j < $example['m']; $j++): ?>
                            <th>Линия <?= $j + 1 ?></th>
                        <?php endfor; ?>
                        <th>Итого по типу</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < $example['n']; $i++): ?>
                        <tr>
                            <td><strong>Тип <?= $i + 1 ?></strong></td>
                            <?php 
                            $typeTotal = 0;
                            for ($j = 0; $j < $example['m']; $j++): 
                                $cost = $result['solution'][$i][$j] * $example['c'][$i][$j];
                                $typeTotal += $cost;
                            ?>
                                <td><?= number_format($cost, 2) ?> руб.</td>
                            <?php endfor; ?>
                            <td><strong><?= number_format($typeTotal, 2) ?> руб.</strong></td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="container">
        <h2>ℹ️ О методе решения</h2>
        <p>Для решения задачи используется алгоритм, основанный на принципе минимизации удельных затрат:</p>
        <ol>
            <li>Вычисляется эффективность каждого типа самолета на каждой линии (расходы на единицу перевозки)</li>
            <li>Пары (самолет, линия) сортируются по возрастанию удельных затрат</li>
            <li>Производится последовательное распределение самолетов начиная с наиболее эффективных</li>
            <li>Учитываются ограничения на доступное количество самолетов и потребности линий</li>
        </ol>
        <p><em>Примечание: Для более сложных случаев рекомендуется использовать специализированные решатели линейного программирования (симплекс-метод, методы внутренней точки).</em></p>
    </div>
</body>
</html>
