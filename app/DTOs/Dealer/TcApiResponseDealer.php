<?php

namespace App\DTOs\Dealer;

class TcApiResponseDealer
{
    public int $id;
    public string $createdAt;
    public string $name;
    public string $email;
    public ?string $primaryEmail;
    public bool $clsfActive;
    public ?bool $isDmsActive;
    public ?bool $isCrmActive;
    public ?bool $isPartsActive;
    public ?bool $isMarketingActive;
    public ?bool $isFmeActive;
    public ?string $profileImage;
    public ?array $website;
    public ?string $from;
    public ?array $permissions;
    public string $logo;
    public ?int $dealer_location_id;
    public ?string $location_name;
    public ?string $region;
    public ?string $city;
    public ?string $zip;

    public static function fromData(array $data): self
    {
        $dto = new self();

        $dto->id = $data['id'];
        $dto->createdAt = $data['created_at'] ?? '';
        $dto->name = $data['name'];
        $dto->email = $data['email'] ?? '';
        $dto->primaryEmail = $data['primary_email'] ?? '';
        $dto->clsfActive = $data['clsf_active'];
        $dto->isDmsActive = $data['is_dms_active'] ?? false;
        $dto->isCrmActive = $data['is_crm_active'] ?? false;
        $dto->isPartsActive = $data['is_parts_active'] ?? false;
        $dto->isMarketingActive = $data['is_marketing_active'] ?? false;
        $dto->isFmeActive = $data['is_fme_active'] ?? false;
        $dto->profileImage = $data['profile_image'] ?? '';
        $dto->website = $data['website'] ?? null;
        $dto->from = $data['from'] ?? '';
        $dto->permissions = $data['permissions'] ?? [];
        $dto->logo = $data['logo'] ?? '';
        $dto->location_name = $data['location_name'] ?? '';
        $dto->dealer_location_id = $data['location_id'] ?? 0;
        $dto->region = $data['region'] ?? '';
        $dto->city = $data['city'] ?? '';
        $dto->zip = $data['zip'] ?? '';

        return $dto;
    }
}
