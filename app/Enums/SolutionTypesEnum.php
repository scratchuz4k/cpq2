<?php

namespace App\Enums;

enum SolutionTypesEnum: string
{
    case ConnectivityEthernet = "3";
    case Mobile = "4";
    case Hardware = "5";

    public function getName(): string
    {
        return match ($this) {
            SolutionTypesEnum::ConnectivityEthernet => "Connectivity - Ethernet",
            SolutionTypesEnum::Mobile => "Mobile",
            SolutionTypesEnum::Hardware => "Hardware",
        };
    }
    public function getColumns(): array
    {
        return match ($this) {
            SolutionTypesEnum::ConnectivityEthernet => [
                ["name" => "Name", "value" => "name"],
                ["name" => "Months", "value" => "contract_length"],
                ["name" => "Quantity", "value" => "quantity"],
                ["name" => "Price", "value" => "price"],
                ["name" => "Margin", "value" => "margin"],
                ["name" => "Discount", "value" => "discount"],
                ["name" => "Total",  "value" => "total"],
                ["name" => "Speed",  "value" => "speed"],
                ["name" => "Bearer",  "value" => "bearer"],
                ["name" => "Provider",  "value" => "provider"],
                ["name" => "SubProvider",  "value" => "subprovider"],
                ["name" => "Quote Reference",  "value" => "quote_ref"],
                ["name" => "Price Plan",  "value" => "price_plan"],

            ],
            SolutionTypesEnum::Mobile => [
                ["name" => "Name", "value" => "name"],
                ["name" => "Months", "value" => "contract_length"],
                ["name" => "Quantity", "value" => "quantity"],
                ["name" => "Price", "value" => "price"],
                ["name" => "Margin", "value" => "margin"],
                ["name" => "Discount", "value" => "discount"],
                ["name" => "Total",  "value" => "total"],
            ],
            SolutionTypesEnum::Hardware => [
                ["name" => "Name", "value" => "name"],
                ["name" => "Months", "value" => "contract_length"],
                ["name" => "Quantity", "value" => "quantity"],
                ["name" => "Price", "value" => "price"],
                ["name" => "Margin", "value" => "margin"],
                ["name" => "Discount", "value" => "discount"],
                ["name" => "Total",  "value" => "total"],
            ],
        };
    }
    public static  function getDefaultTables(): array
    {
        return [
            [
                "code" => "Recurring",
                "columns" => [
                    ["name" => "Name", "value" => "name", "is_group" => true, "is_active" => true],
                    ["name" => "Months", "value" => "contract_length", "is_active" => true],
                    ["name" => "Quantity", "value" => "quantity", "is_active" => true],
                    ["name" => "Price", "value" => "price", "is_active" => true],
                    ["name" => "Margin", "value" => "margin", "is_active" => true],
                    ["name" => "Discount", "value" => "discount", "is_active" => true],
                    ["name" => "Total",  "value" => "total", "is_active" => true],
                ]
            ],
            [
                "code" => "One-Off",
                "columns" => [
                    ["name" => "Name", "value" => "name", "is_group" => true, "is_active" => true],
                    ["name" => "Months", "value" => "contract_length", "is_active" => true],
                    ["name" => "Quantity", "value" => "quantity", "is_active" => true],
                    ["name" => "Price", "value" => "price", "is_active" => true],
                    ["name" => "Margin", "value" => "margin", "is_active" => true],
                    ["name" => "Discount", "value" => "discount", "is_active" => true],
                    ["name" => "Total",  "value" => "total", "is_active" => true],
                ]
            ]
        ];
    }
}
