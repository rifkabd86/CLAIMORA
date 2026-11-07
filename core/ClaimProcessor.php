<?php
namespace Core;

/**
 * Interface ClaimEvaluatorInterface (Abstraction)
 */
interface ClaimEvaluatorInterface
{
    public function evaluate(array $policy, float $damageCost, string $incidentDate): array;
}

/**
 * Abstract Base Class for Claim Business Rules (Inheritance + Encapsulation)
 */
abstract class BaseClaimEvaluator implements ClaimEvaluatorInterface
{
    protected float $maxCoverage;
    protected string $policyStart;
    protected string $policyEnd;
    protected string $policyStatus;

    public function __construct(array $policy)
    {
        $this->maxCoverage = (float)($policy['coverage_amount'] ?? 0);
        $this->policyStart = $policy['start_date'] ?? '';
        $this->policyEnd   = $policy['end_date'] ?? '';
        $this->policyStatus = $policy['status'] ?? 'Aktif';
    }

    /**
     * Encapsulated date validation rule
     */
    protected function isIncidentWithinPolicyPeriod(string $incidentDate): bool
    {
        $inc = strtotime($incidentDate);
        $start = strtotime($this->policyStart);
        $end = strtotime($this->policyEnd);

        return ($inc >= $start && $inc <= $end);
    }

    /**
     * Check if policy is active
     */
    protected function isPolicyActive(): bool
    {
        return strtolower($this->policyStatus) === 'aktif';
    }
}

/**
 * Concrete Evaluator for Vehicle Claims (Polymorphism)
 */
class VehicleClaimEvaluator extends BaseClaimEvaluator
{
    /**
     * Polymorphic implementation of business rules
     */
    public function evaluate(array $policy, float $damageCost, string $incidentDate): array
    {
        $errors = [];
        $warnings = [];

        // Rule 1: Policy must be currently active
        if (!$this->isPolicyActive()) {
            $errors[] = "Polis tidak dalam status Aktif (Status saat ini: {$this->policyStatus}). Klaim tidak dapat diproses.";
        }

        // Rule 2: Incident date must fall within protection period
        if (!$this->isIncidentWithinPolicyPeriod($incidentDate)) {
            $formattedInc = date('d/m/Y', strtotime($incidentDate));
            $formattedStart = date('d/m/Y', strtotime($this->policyStart));
            $formattedEnd = date('d/m/Y', strtotime($this->policyEnd));
            $errors[] = "Tanggal kejadian ({$formattedInc}) berada di luar masa berlaku proteksi polis ({$formattedStart} s/d {$formattedEnd}).";
        }

        // Rule 3: Damage cost check against coverage limit
        $coverageRatio = $this->maxCoverage > 0 ? ($damageCost / $this->maxCoverage) : 1;
        if ($damageCost > $this->maxCoverage) {
            $warnings[] = "Estimasi biaya klaim (Rp " . number_format($damageCost, 0, ',', '.') . ") melebihi batas pertanggungan polis (Rp " . number_format($this->maxCoverage, 0, ',', '.') . "). Selisih biaya tidak ditanggung asuransi.";
        }

        // Rule 4: Total loss threshold (e.g. > 75% of coverage)
        $isTotalLoss = $coverageRatio >= 0.75;
        if ($isTotalLoss) {
            $warnings[] = "Nilai kerusakan mencapai " . round($coverageRatio * 100, 1) . "% dari harga pertanggungan, berpotensi dikategorikan Total Loss.";
        }

        return [
            'isValid'        => empty($errors),
            'errors'         => $errors,
            'warnings'       => $warnings,
            'coverage_ratio' => $coverageRatio,
            'is_total_loss'  => $isTotalLoss,
            'max_coverage'   => $this->maxCoverage
        ];
    }
}
