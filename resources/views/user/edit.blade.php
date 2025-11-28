@extends('layouts.admin')

@section('page-title')
    {{__('Edit User')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('users.index')}}">{{__('User')}}</a></li>
    <li class="breadcrumb-item">{{__('Edit')}}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">{{__('Edit User')}}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('users.update', $user->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mobile" class="form-label">{{ __('Mobile') }}</label>
                                    <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mobile', $user->mobile) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                </div>
                            </div>
                        </div>

                        @if(\Auth::user()->type != 'super admin')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role" class="form-label">{{ __('Role') }} <span class="text-danger">*</span></label>
                                    <select class="form-control" id="role" name="role" required>
                                        <option value="">{{ __('Select Role') }}</option>
                                        @foreach($roles as $id => $role)
                                            <option value="{{ $id }}" {{ $user->type == $role ? 'selected' : '' }}>{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                           
                        @else
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role" class="form-label">{{ __('Role') }} <span class="text-danger">*</span></label>
                                    <select class="form-control" id="role" name="role" required>
                                        <option value="">{{ __('Select Role') }}</option>
                                        @foreach($roles as $id => $role)
                                            <option value="{{ $id }}" {{ $user->type == $role ? 'selected' : '' }}>{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="companies" class="form-label">{{ __('Companies') }} <span class="text-danger">*</span></label>
                                    <select class="form-control" id="companies" name="companies[]" multiple="multiple" required>
                                        @if(isset($companies))
                                            @foreach($companies as $id => $company)
                                                <option value="{{ $id }}" {{ in_array($id, $userCompanies) ? 'selected' : '' }}>{{ $company }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="form-label">{{ __('Password') }}</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <small class="form-text text-muted">{{ __('Leave blank to keep current password') }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                </div>
                            </div>
                        </div>

                        <!-- Profile Picture Section -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Profile Picture') }}</label>
                                    
                                    <!-- Current Profile Picture -->
                                    @if($user->avatar)
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Current Profile Picture') }}</label>
                                            <div>
                                                <img src="{{ asset($user->avatar) }}" alt="Profile Picture" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="profile_picture" name="profile_picture" accept="image/*">
                                        <label class="custom-file-label" for="profile_picture">
                                            {{ $user->avatar ? __('Change file') : __('Choose file') }}
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        {{ __('Max Width/Height: 500px * 500px & Size: 500kb') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Custom Fields -->
                        @if(!$customFields->isEmpty())
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <h5>{{ __('Additional Information') }}</h5>
                                </div>
                                @foreach($customFields as $field)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customField[{{$field->id}}]" class="form-label">{{ $field->name }}</label>
                                            @php
                                                $value = isset($user->customField[$field->id]) ? $user->customField[$field->id] : '';
                                            @endphp
                                            @if($field->type == 'text')
                                                <input type="text" class="form-control" id="customField[{{$field->id}}]" name="customField[{{$field->id}}]" value="{{ old('customField['.$field->id.']', $value) }}">
                                            @elseif($field->type == 'number')
                                                <input type="number" class="form-control" id="customField[{{$field->id}}]" name="customField[{{$field->id}}]" value="{{ old('customField['.$field->id.']', $value) }}">
                                            @elseif($field->type == 'date')
                                                <input type="date" class="form-control" id="customField[{{$field->id}}]" name="customField[{{$field->id}}]" value="{{ old('customField['.$field->id.']', $value) }}">
                                            @elseif($field->type == 'textarea')
                                                <textarea class="form-control" id="customField[{{$field->id}}]" name="customField[{{$field->id}}]">{{ old('customField['.$field->id.']', $value) }}</textarea>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="form-group text-end mt-4">
                            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">{{ __('Close') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<!-- Select2 CSS -->
<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
    }
    .select2-container .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #ced4da;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        color: #495057;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }
    
    /* Search box styling */
    .select2-search--dropdown {
        display: block !important;
        padding: 8px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .select2-search--dropdown .select2-search__field {
        width: 100% !important;
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .select2-search--dropdown .select2-search__field:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    /* Ensure dropdown has enough width */
    .select2-container .select2-dropdown {
        min-width: 300px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Dropdown options styling */
    .select2-results__option {
        padding: 8px 12px;
    }
    
    .select2-results__option--highlighted {
        background-color: #007bff !important;
        color: white !important;
    }

    /* Current profile picture styling */
    .img-thumbnail {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 0.25rem;
    }
</style>
@endpush

@push('scripts')
<!-- jQuery -->
<script src="{{ asset('js/jquery.min.js') }}"></script>
<!-- Select2 JS -->
<script src="{{ asset('js/select2.min.js') }}"></script>

<script>
    $(document).ready(function() {
        // File input label update
        $('#profile_picture').on('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : '{{ $user->avatar ? __("Change file") : __("Choose file") }}';
            $(this).next('.custom-file-label').text(fileName);
        });

        // Initialize Role dropdown with search
        $('#role').select2({
            width: '100%',
            placeholder: "Select Role",
            allowClear: true,
            dropdownParent: $('#role').closest('.form-group'),
            minimumResultsForSearch: 0,
            language: {
                noResults: function() {
                    return "No results found";
                },
                searching: function() {
                    return "Searching...";
                }
            }
        });

        // Initialize Companies dropdown with search
        $('#companies').select2({
            width: '100%',
            placeholder: "Select Companies",
            allowClear: true,
            closeOnSelect: false,
            dropdownParent: $('#companies').closest('.form-group'),
            minimumResultsForSearch: 0,
            language: {
                noResults: function() {
                    return "No results found";
                },
                searching: function() {
                    return "Searching...";
                }
            }
        });

        // Focus on search field when dropdown opens
        $(document).on('select2:open', function(e) {
            setTimeout(function() {
                const searchField = document.querySelector('.select2-container--open .select2-search__field');
                if (searchField) {
                    searchField.focus();
                    
                    const targetId = e.target.id;
                    if (targetId === 'role') {
                        searchField.placeholder = 'Search roles...';
                    } else if (targetId === 'companies') {
                        searchField.placeholder = 'Search companies...';
                    } else {
                        searchField.placeholder = 'Search...';
                    }
                }
            }, 100);
        });
    });
</script>
@endpush