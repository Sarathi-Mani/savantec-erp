
<?php
   // $profile=asset(Storage::url('uploads/avatar/'));
    $profile=\App\Models\Utility::get_file('uploads/avatar');
?>
<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage User')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('User')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('action-btn'); ?>
    <div class="float-end">
        <?php if(\Auth::user()->type == 'company' || \Auth::user()->type == 'HR'): ?>
            <a href="<?php echo e(route('user.userlog')); ?>" class="btn btn-primary btn-sm <?php echo e(Request::segment(1) == 'user'); ?>"
                   data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo e(__('User Logs History')); ?>"><i class="ti ti-user-check"></i>
            </a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create user')): ?>
            <a href="<?php echo e(route('users.create')); ?>" data-bs-toggle="tooltip" title="<?php echo e(__('Create')); ?>"  class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable">
                <thead>
                    <tr>
                        <th><?php echo e(__('Name')); ?></th>
                        <th><?php echo e(__('Email')); ?></th>
                        <th><?php echo e(__('Type')); ?></th>
                        <th><?php echo e(__('Last Login')); ?></th>
                        <th><?php echo e(__('Status')); ?></th>
                        <?php if(\Auth::user()->type == 'super admin'): ?>
                            <th><?php echo e(__('Plan')); ?></th>
                            <th><?php echo e(__('Plan Expired')); ?></th>
                        <?php endif; ?>
                        <th><?php echo e(__('Action')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?php echo e((!empty($user->avatar))? asset(Storage::url("uploads/avatar/".$user->avatar)): asset(Storage::url("uploads/avatar/avatar.png"))); ?>" 
                                     class="rounded-circle me-2" width="30" height="30">
                                <?php echo e($user->name); ?>

                            </div>
                        </td>
                        <td><?php echo e($user->email); ?></td>
                        <td>
                            <div class="badge bg-primary p-2 px-3 rounded">
                                <?php echo e(ucfirst($user->type)); ?>

                            </div>
                        </td>
                        <td><?php echo e((!empty($user->last_login_at)) ? $user->last_login_at : ''); ?></td>
                        <td>
                            <?php if($user->delete_status==0): ?>
                                <span class="badge bg-danger"><?php echo e(__('Inactive')); ?></span>
                            <?php else: ?>
                                <span class="badge bg-success"><?php echo e(__('Active')); ?></span>
                            <?php endif; ?>
                        </td>
                        <?php if(\Auth::user()->type == 'super admin'): ?>
                            <td><?php echo e(!empty($user->currentPlan)?$user->currentPlan->name:''); ?></td>
                            <td><?php echo e(!empty($user->plan_expire_date) ? \Auth::user()->dateFormat($user->plan_expire_date): __('Lifetime')); ?></td>
                        <?php endif; ?>
                        <td>
                            <?php if(Gate::check('edit user') || Gate::check('delete user')): ?>
                                <?php if($user->is_active == 1): ?>
                                    <div class="d-flex">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit user')): ?>
                                            <div class="action-btn bg-light-secondary ms-2">
                                                <a href="<?php echo e(route('users.edit', $user->id)); ?>" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="<?php echo e(__('Edit')); ?>">
                                                    <i class="ti ti-pencil text-dark"></i>
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete user')): ?>
                                            <div class="action-btn bg-light-secondary ms-2">
                                                <?php echo Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user['id']],'id'=>'delete-form-'.$user['id']]); ?>

                                                <a href="#!" class="mx-3 btn btn-sm d-inline-flex align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php if($user->delete_status!=0): ?><?php echo e(__('Delete')); ?> <?php else: ?> <?php echo e(__('Restore')); ?><?php endif; ?>" data-confirm="<?php echo e(__('Are You Sure?')); ?>" data-text="<?php echo e(__('This action can not be undone. Do you want to continue?')); ?>" data-confirm-yes="delete-form-<?php echo e($user['id']); ?>">
                                                    <i class="ti ti-archive text-dark"></i>
                                                </a>
                                                <?php echo Form::close(); ?>

                                            </div>
                                        <?php endif; ?>

                                        <div class="action-btn bg-light-secondary ms-2">
                                            <a href="#!" data-url="<?php echo e(route('users.reset',\Crypt::encrypt($user->id))); ?>" data-ajax-popup="true" data-size="md" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="<?php echo e(__('Reset Password')); ?>">
                                                <i class="ti ti-adjustments text-dark"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <a href="#" class="btn btn-sm btn-light-secondary"><i class="ti ti-lock"></i></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Add click event for delete/restore buttons
    document.addEventListener('DOMContentLoaded', function() {
        const passButtons = document.querySelectorAll('.bs-pass-para');
        passButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const formId = this.getAttribute('data-confirm-yes');
                const confirmText = this.getAttribute('data-text');
                const confirmMessage = this.getAttribute('data-confirm');
                
                if (confirm(confirmMessage + '\n\n' + confirmText)) {
                    document.getElementById(formId).submit();
                }
            });
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\ADMIN\Projects\savantec\resources\views/user/index.blade.php ENDPATH**/ ?>