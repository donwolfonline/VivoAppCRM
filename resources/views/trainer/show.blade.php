<table class="footable-details table table-hover toggle-circle custom_table_responsive">
    <tbody>
    <tr>
        <th>{{__('First Name')}}</th>
        <td class="custom-table-td" style="display: table-cell;">{{$trainer->firstname}}</td>
    </tr>
    <tr>
        <th>{{__('Last Name')}}</th>
        <td class="custom-table-td" style="display: table-cell;">{{$trainer->lastname}}</td>
    </tr>
    <tr>
        <th>{{__('Contact Number')}}</th>
        <td class="custom-table-td" style="display: table-cell;">{{$trainer->contact}}</td>
    </tr>
    <tr>
        <th>{{__('Email')}}</th>
        <td class="custom-table-td" style="display: table-cell;">{{$trainer->email}}</td>
    </tr>
    <tr>
        <th>{{__('Expertise')}}</th>
        <td class="custom-table-td" style="display: table-cell;">{{$trainer->expertise}}</td>
    </tr>
    <tr>
        <th>{{__('Address')}}</th>
        <td class="custom-table-td" style="display: table-cell;white-space: normal">{{$trainer->address}}</td>
    </tr>
    </tbody>
</table>
