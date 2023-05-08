<?php

namespace App\DTOs\Dealer;

class TcApiResponseDealer
{
    public int $id;
    public string $createdAt;
    public string $name;
    public string $email;
    public string $primaryEmail;
    public bool $clsfActive;
    public bool $isDmsActive;
    public bool $isCrmActive;
    public bool $isPartsActive;
    public bool $isMarketingActive;
    public bool $isFmeActive;
    public ?string $profileImage;
    public ?array $website;
    public ?string $from;
    public array $permissions;
    public array $logo;

    public static function fromData(array $data): self
    {
        $dto = new self();

        $dto->id = $data['id'];
        $dto->createdAt = $data['created_at'];
        $dto->name = $data['name'];
        $dto->email = $data['email'];
        $dto->primaryEmail = $data['primary_email'];
        $dto->clsfActive = $data['clsf_active'];
        $dto->isDmsActive = $data['is_dms_active'];
        $dto->isCrmActive = $data['is_crm_active'];
        $dto->isPartsActive = $data['is_parts_active'];
        $dto->isMarketingActive = $data['is_marketing_active'];
        $dto->isFmeActive = $data['is_fme_active'];
        $dto->profileImage = $data['profile_image'];
        $dto->website = $data['website'];
        $dto->from = $data['from'];
        $dto->permissions = $data['permissions'];
        $dto->logo = $data['logo'];

        return $dto;
    }
}
