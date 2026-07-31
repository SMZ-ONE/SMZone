protected function getStats(): array
{
    return [
        Stat::make('Revenue', '€0')
            ->description('Coming Soon'),

        Stat::make('Products', '0')
            ->description('byCOSMETIQ'),

        Stat::make('AI Score', '0%')
            ->description('Not available yet'),
    ];
}