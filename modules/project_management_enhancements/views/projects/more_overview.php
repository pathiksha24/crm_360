<?php /** @var array $owners */ ?>
<dl class="tw-grid tw-grid-cols-1 tw-gap-x-4 tw-gap-y-3 sm:tw-grid-cols-2">
    <div class="sm:tw-col-span-1 project-overview-customer">
        <dt class="tw-text-sm tw-font-normal tw-text-neutral-500">
			<?= _l('project_owners'); ?>
        </dt>
        <dd class="tw-mt-1 tw-text-sm tw-text-neutral-700 tw-font-medium">
			<?php foreach ($owners as $index => $owner): ?>
                <a href="<?= admin_url('staff/member/'.$owner['staff_id']) ?>">
					<?= e(sprintf('%s %s', $owner['firstname'], $owner['lastname'])); ?>
                </a><?php echo ($index < count($owners) - 1 ? ', ' : '') ?>
			<?php endforeach; ?>
        </dd>
    </div>
</dl>