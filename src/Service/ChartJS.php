<?php

namespace App\Service;

use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class ChartJS
{
    public function __construct(
        private ChartBuilderInterface $chartBuilder,
        private $label = [],
        private $data = [],
    )
    {
    }

    public function createPieChart($labelName, $value): Chart
    {
        $this->label = [];
        $this->data = [];

        foreach ($value as $val) {
            $keys = array_keys($val);
            $this->label[] = $val[$keys[0]];
            $this->data[] = $val[$keys[1]];
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_PIE);
        $chart->setData([
            'labels' => $this->label,
            'datasets' => [
                [
                    'label' => $labelName,
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(205, 99, 199)',
                        'rgb(99, 01, 05)',
                    ],
                    'data' => $this->data,
                ],
            ],
        ]);
        $chart->setOptions([
        ]);
        return $chart;
    }

    public function createLineChart($labelName,$value): Chart
    {
        $this->label = [];
        $this->data = [];

        foreach ($value as $val) {
            $keys = array_keys($val);
            $this->label[] = $val[$keys[0]];
            $this->data[] = $val[$keys[1]];
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $this->label,
            'datasets' => [
                [
                    'label' => $labelName,
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $this->data,
                ],
            ],
        ]);

        return $chart;
    }

    public function createLineCircleChart($labelName,$value): Chart
    {
        $this->label = [];
        $this->data = [];

        foreach ($value as $val) {
            $keys = array_keys($val);
            $this->label[] = $val[$keys[0]];
            $this->data[] = $val[$keys[1]];
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $this->label,
            'datasets' => [
                [
                    'label' => $labelName,
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $this->data,
                    'pointStyle' => 'circle',
                    'pointRadius' => 10,
                    'pointHoverRadius' => 15
                ],
            ],
        ]);

        return $chart;
    }

}