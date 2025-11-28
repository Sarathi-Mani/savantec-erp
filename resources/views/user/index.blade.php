@extends('layouts.admin')
@php
   // $profile=asset(Storage::url('uploads/avatar/'));
    $profile=\App\Models\Utility::get_file('uploads/avatar');
@endphp
@section('page-title')
    {{__('Manage User')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('User')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'HR')
            <a href="{{ route('user.userlog') }}" class="btn btn-primary btn-sm {{ Request::segment(1) == 'user' }}"
                   data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('User Logs History') }}"><i class="ti ti-user-check"></i>
            </a>
        @endif
        @can('create user')
            <a href="{{ route('users.create') }}" data-bs-toggle="tooltip" title="{{__('Create')}}"  class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section('content')
    <div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable">
                <thead>
                    <tr>
                        <th>{{__('Name')}}</th>
                        <th>{{__('Email')}}</th>
                        <th>{{__('Type')}}</th>
                        <th>{{__('Last Login')}}</th>
                        <th>{{__('Status')}}</th>
                        @if(\Auth::user()->type == 'super admin')
                            <th>{{__('Plan')}}</th>
                            <th>{{__('Plan Expired')}}</th>
                        @endif
                        <th>{{__('Action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{(!empty($user->avatar))? asset(Storage::url("uploads/avatar/".$user->avatar)): asset(Storage::url("uploads/avatar/avatar.png"))}}" 
                                     class="rounded-circle me-2" width="30" height="30">
                                {{ $user->name }}
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <div class="badge bg-primary p-2 px-3 rounded">
                                {{ ucfirst($user->type) }}
                            </div>
                        </td>
                        <td>{{ (!empty($user->last_login_at)) ? $user->last_login_at : '' }}</td>
                        <td>
                            @if($user->delete_status==0)
                                <span class="badge bg-danger">{{__('Soft Deleted')}}</span>
                            @else
                                <span class="badge bg-success">{{__('Active')}}</span>
                            @endif
                        </td>
                        @if(\Auth::user()->type == 'super admin')
                            <td>{{!empty($user->currentPlan)?$user->currentPlan->name:''}}</td>
                            <td>{{!empty($user->plan_expire_date) ? \Auth::user()->dateFormat($user->plan_expire_date): __('Lifetime')}}</td>
                        @endif
                        <td>
                            @if(Gate::check('edit user') || Gate::check('delete user'))
                                @if($user->is_active == 1)
                                    <div class="action-btn bg-light-secondary ms-2">
                                        <div class="dropdown">
                                            <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                @can('edit user')
                                                    <a href="{{ route('users.edit', $user->id) }}" class="dropdown-item" data-bs-original-title="{{__('Edit User')}}">
                                                        <i class="ti ti-pencil"></i>
                                                        <span>{{__('Edit')}}</span>
                                                    </a>
                                                @endcan

                                                @can('delete user')
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user['id']],'id'=>'delete-form-'.$user['id']]) !!}
                                                    <a href="#!" class="dropdown-item bs-pass-para">
                                                        <i class="ti ti-archive"></i>
                                                        <span> @if($user->delete_status!=0){{__('Delete')}} @else {{__('Restore')}}@endif</span>
                                                    </a>
                                                    {!! Form::close() !!}
                                                @endcan

                                                <a href="#!" data-url="{{route('users.reset',\Crypt::encrypt($user->id))}}" data-ajax-popup="true" data-size="md" class="dropdown-item" data-bs-original-title="{{__('Reset Password')}}">
                                                    <i class="ti ti-adjustments"></i>
                                                    <span>{{__('Reset Password')}}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <a href="#" class="btn btn-sm btn-light-secondary"><i class="ti ti-lock"></i></a>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
