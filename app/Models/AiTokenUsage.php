<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiTokenUsage extends Model
{
    protected $table = 'ai_token_usages';

    protected $fillable = [
        'model',
        'operation',
        'prompt_tokens',
        'candidate_tokens',
        'total_tokens',
        'cost_usd',
        'licitacion_id',
        'market',
        'date'
    ];

    protected $casts = [
        'prompt_tokens' => 'integer',
        'candidate_tokens' => 'integer',
        'total_tokens' => 'integer',
        'cost_usd' => 'float',
        'date' => 'date:Y-m-d'
    ];

    /**
     * Tarifas oficiales Google Gemini (por 1M tokens en USD)
     * Gemini 3.8 Flash / 1.5 Flash:
     * Input: $0.075 / 1M tokens
     * Output: $0.300 / 1M tokens
     */
    public const COST_INPUT_PER_M = 0.075;
    public const COST_OUTPUT_PER_M = 0.300;

    public static function calculateCost(int $promptTokens, int $candidateTokens): float
    {
        $inputCost = ($promptTokens / 1_000_000) * self::COST_INPUT_PER_M;
        $outputCost = ($candidateTokens / 1_000_000) * self::COST_OUTPUT_PER_M;
        return round($inputCost + $outputCost, 6);
    }

    public static function recordUsage(
        string $model,
        int $promptTokens,
        int $candidateTokens,
        ?int $licitacionId = null,
        string $market = 'CO',
        string $operation = 'evaluacion_afinidad'
    ): self {
        $total = $promptTokens + $candidateTokens;
        $cost = self::calculateCost($promptTokens, $candidateTokens);

        return self::create([
            'model' => $model,
            'operation' => $operation,
            'prompt_tokens' => $promptTokens,
            'candidate_tokens' => $candidateTokens,
            'total_tokens' => $total,
            'cost_usd' => $cost,
            'licitacion_id' => $licitacionId,
            'market' => strtoupper($market),
            'date' => now()->toDateString()
        ]);
    }
}

