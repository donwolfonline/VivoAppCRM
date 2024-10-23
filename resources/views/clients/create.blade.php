{{ Form::model($selected, array('route' => array('client.store.permission', $project_id,$client_id), 'method' => 'POST')) }}
<div>
    <div class="row">
        <div class="col-md-12">
            <div class="table-border-style">
                <div class="table-responsive">
                @if(!empty($permissions))
                    <table class="table mb-0" id="dataTable-1">
                        <thead>
                        <tr>
                            <th>{{__('Module')}} </th>
                            <th>{{__('Permissions')}} </th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $modules=['milestone','task','checklist','activity','uploading','bug report','timesheet'];
                        @endphp
                        @foreach($modules as $module)
                            <tr>
                                <td>{{ ucfirst($module) }}</td>
                                <td>
                                    <div class="row cust-checkbox-row">
                                        @if(in_array('create '.$module,(array) $permissions))
                                            @if($key = array_search('create '.$module,$permissions))
                                                <div class="col-3 custom-control custom-checkbox">
                                                    {{Form::checkbox('permissions[]','create '.$module,in_array('create '.$module,(array) $selected), ['class'=>'form-check-input','id' =>'permission'.$key])}}
                                                    {{Form::label('permission'.$key,'Create',['class'=>'form-check-label'])}}<br>
                                                </div>
                                            @endif
                                        @endif
                                        @if(in_array('edit '.$module,(array) $permissions))
                                            @if($key = array_search('edit '.$module,$permissions))
                                                <div class="col-3 custom-control custom-checkbox">
                                                    {{Form::checkbox('permissions[]','edit '.$module,in_array('edit '.$module,(array) $selected), ['class'=>'form-check-input','id' =>'permission'.$key])}}
                                                    {{Form::label('permission'.$key,'Edit',['class'=>'form-check-label'])}}<br>
                                                </div>
                                            @endif
                                        @endif
                                        @if(in_array('delete '.$module,(array) $permissions))
                                            @if($key = array_search('delete '.$module,$permissions))
                                                <div class="col-3 custom-control custom-checkbox">
                                                    {{Form::checkbox('permissions[]','delete '.$module,in_array('delete '.$module,(array) $selected), ['class'=>'form-check-input','id' =>'permission'.$key])}}
                                                    {{Form::label('permission'.$key,'Delete',['class'=>'form-check-label'])}}<br>
                                                </div>
                                            @endif
                                        @endif
                                        @if(in_array('show '.$module,(array) $permissions))
                                            @if($key = array_search('show '.$module,$permissions))
                                                <div class="col-3 custom-control custom-checkbox">
                                                    {{Form::checkbox('permissions[]','show '.$module,in_array('show '.$module,(array) $selected), ['class'=>'form-check-input','id' =>'permission'.$key])}}
                                                    {{Form::label('permission'.$key,'Show',['class'=>'form-check-label'])}}<br>
                                                </div>
                                            @endif
                                        @endif
                                        @if(in_array('move '.$module,(array) $permissions))
                                            @if($key = array_search('move '.$module,$permissions))
                                                <div class="col-3 custom-control custom-checkbox">
                                                    {{Form::checkbox('permissions[]','move '.$module,in_array('move '.$module,(array) $selected), ['class'=>'form-check-input','id' =>'permission'.$key])}}
                                                    {{Form::label('permission'.$key,'Move',['class'=>'form-check-label'])}}<br>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-secondary btn-light" data-dismiss="modal">
    <input type="submit" value="{{__('Save')}}" class="btn btn-primary ms-2">
</div>
{{Form::close()}}
