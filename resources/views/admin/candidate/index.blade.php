@extends('admin.layouts.admin_master')

@section('content')
    <div class="col-12 mt-3 abs">    
        <div class="card" id="id02">
            <div class="card-header d-flex justify-content-between">
                <div class="left flex-grow-1">
                    <h3 class="card-title">Candidate List</h3>
                </div>

                @if (session('name'))
                <div class="middle flex-grow-1">
                    <div class="alert alert-success mb-0 text-white" style="width: 50%" role="alert">
                        <h3>{{session('name')}} has been added.</h3>
                    </div>
                </div>
                @endif

                @if (session('edited'))
                <div class="middle flex-grow-1">
                    <div class="alert alert-success mb-0 text-white" style="width: 50%" role="alert">
                        <h3>Updated Successfully.</h3>
                    </div>
                </div>
                @endif

                @if (session('wrong'))
                <div class="middle flex-grow-1">
                    <div class="alert alert-success mb-0 text-white" style="width: 50%" role="alert">
                        <h3>Cannot Export. There are not sufficient candidates in some posts. </h3>
                    </div>
                </div>
                @endif

                <div class="right">
                   <button class="btn btn-warning" onclick="window.location.href = '{{ route('admin.candidate.createview') }}';"><i class="fas fa-plus-square fa-2x mr-2"></i><span style="vertical-align: super;">Add New Candidate</span></a> </button>
                    <span data-href="exportdata" id="export" class="btn btn-success " onclick="exportTasks(event.target);" style="padding: 11px;">Export Data</span>
                    <span data-href="exportimg" id="export" class="btn btn-danger " onclick="exportTasks(event.target);" style="padding: 11px;">Export Image</span>
                </div>
            
            
            </div>

              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>S.no</th>
                    <th>Candidate Id</th>
                    <th>Post Name</th>
                    <th>Nepali Name</th>
                    <th>English Name</th>
                    <th>Photo</th>
                    <th>Operations</th>
                </tr>
                  </thead>
                  <tbody>
                    @foreach($candidates as $key=>$candidate)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ sprintf("%03d",$candidate['id'])}}</td>
                            <td>{{ $candidate->getPosts->post_name }}</td>
                            <td>{{ $candidate['nepali_name'] }}</td>
                            <td>{{ $candidate['english_name'] }}</td>
                            <td><img src="{{$candidate['image']}}" alt="" srcset="" width="100px" height="auto"></td>
                            <td style="width:15%">
                                <span><a href="{{ url('candidate/edit/'.$candidate['id'])}}"><button><i class="fas fa-edit m-3 fa-1x" ></i></button></a></span>
                                <span><a href="{{ route('admin.candidate.delete',$candidate['id'])}}" id="delete"><i class="fas fa-trash-alt text-danger fa-1x"></i></a></span>
                            </td>
                        
                        </tr>

                    @endforeach
                 
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->

        </div>
        <!-- /.card -->
        
    </div>
@endsection