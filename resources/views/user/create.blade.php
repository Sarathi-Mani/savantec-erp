@extends('layouts.admin')

@section('page-title')
    {{__('Create User')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('users.index')}}">{{__('User')}}</a></li>
    <li class="breadcrumb-item">{{__('Create')}}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">{{__('Create User')}}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
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
                                            <option value="{{ $id }}" {{ old('role') == $id ? 'selected' : '' }}>{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="form-label">{{ __('Password') }} <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="form-label">{{ __('Password') }} <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Custom Fields -->
                        @if(!$customFields->isEmpty())
                            <div class="row">
                                <div class="col-md-12">
                                    <h5>{{ __('Additional Information') }}</h5>
                                </div>
                                @foreach($customFields as $field)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customField[{{$field->id}}]" class="form-label">{{ $field->name }}</label>
                                            @if($field->type == 'text')
                                                <input type="text" class="form-control" id="customField[{{$field->id}}]" name="customField[{{$field->id}}]" value="{{ old('customField['.$field->id.']') }}">
                                            @elseif($field->type == 'number')
                                                <input type="number" class="form-control" id="customField[{{$field->id}}]" name="customField[{{$field->id}}]" value="{{ old('customField['.$field->id.']') }}">
                                            @elseif($field->type == 'date')
                                                <input type="date" class="form-control" id="customField[{{$field->id}}]" name="customField[{{$field->id}}]" value="{{ old('customField['.$field->id.']') }}">
                                            @elseif($field->type == 'textarea')
                                                <textarea class="form-control" id="customField[{{$field->id}}]" name="customField[{{$field->id}}]">{{ old('customField['.$field->id.']') }}</textarea>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="form-group text-end mt-4">
                            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">{{ __('Close') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection