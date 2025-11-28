@extends('layouts.admin')
@php
   // $profile=asset(Storage::url('uploads/avatar/'));
    $profile=\App\Models\Utility::get_file('uploads/avatar/');
@endphp
@section('page-title')
    {{__('Manage Company')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Company')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        <a href="{{ route('company.create') }}" data-bs-toggle="tooltip" title="{{__('Create')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                 
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('Email')}}</th>
                                    <th>{{__('Mobile')}}</th>
                                  <th>{{__('Details')}}</th>
                                  
                                    <th>{{__('Status')}}</th>
                                    <th>{{__('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($companies as $company)
                                    <tr>
                                       
                                        <td>{{ $company->name }}</td>
                                        <td>{{ $company->email }}</td>
                                        <td>{{ $company->mobile ?? '-' }}</td>
                                        <td>{{ $company->details ?? '-' }}</td>
                                        <td>
                                            @if($company->delete_status==0)
                                                <span class="badge bg-danger">{{__('Inactive')}}</span>
                                            @else
                                                <span class="badge bg-success">{{__('Active')}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($company->is_active == 1)
                                                <div class="d-flex">
                                                    <div class="action-btn bg-light-secondary ms-2">
                                                        <a href="{{ route('company.edit', $company->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}">
                                                            <i class="ti ti-pencil text-dark"></i>
                                                        </a>
                                                    </div>
                                                  
                                                </div>
                                            @else
                                                <a href="#" class="btn btn-sm btn-light-secondary"><i class="ti ti-lock"></i></a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Add click event for delete buttons
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

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush