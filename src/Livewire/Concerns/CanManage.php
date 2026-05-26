<?php

namespace Wsmallnews\Preference\Livewire\Concerns;

trait CanManage
{
    public bool $manageMode = false;

    public array $selected = [];

    public function toggleManageMode(): void
    {
        $this->manageMode = ! $this->manageMode;
        $this->selected = [];
    }

    public function toggleSelectAll(): void
    {
        $currentItems = $this->getCurrents();

        if (count($this->selected) === $currentItems->count()) {
            $this->selected = [];
        } else {
            $this->selected = $currentItems->pluck('id')->toArray();
        }
    }

    public function toggleItem(int $id): void
    {
        if (in_array($id, $this->selected)) {
            $this->selected = array_values(array_diff($this->selected, [$id]));
        } else {
            $this->selected[] = $id;
        }
    }

    public function getSelectedCount(): int
    {
        return count($this->selected);
    }

    public function isSelected(int $id): bool
    {
        return in_array($id, $this->selected);
    }

    abstract protected function getCurrents();
}
