<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\Post;
use DB;
use Image;

class CandidateController extends Controller
{
    public function index()
    {
        $candidates = Candidate::all();
        return view('admin.candidate.index',compact('candidates'));
    }
    public function createView()
    {
        $posts = Post::all();
        return view('admin.candidate.create',compact('posts'));

    }

    public function create(Request $req)
    {
        $postid = Post::find($req->input('postname'))->id;


        $candidate = new Candidate;
        $candidate['post_id'] = $postid;
        $candidate['nepali_name'] = $req->input('nepname');
        $candidate['english_name'] = $req->input('engname');

        // $pid = sprintf("%02d",$postid);
        // $canid = sprintf("%03d",$candidate['id']);

        if ($req->hasFile('photo')) {
            $file = $req->file('photo');
            // $image_one = $pid.$canid.".png";
            $image_one = uniqid().".png";
            $image = Image::make($file)->resize(250,185)->save('uploads/candidate/'.$image_one);
            $enc =  $image->encode('data-url');
            $candidate['image'] = $enc;

        }

        $candidate->save();


        // Candidate::create($candidate);

        $data = $req->input('name');
        $req->session()->flash('name',$data);
        return redirect(route('admin.candidate.index'));
    }

    public function delete($id)
    {
        $candidate = Candidate::find($id);
        $candidate->delete();
        return redirect()->back();
    }

    public function editForm($id)
    {
        $candidate = Candidate::find($id);
        $posts = Post::all();
        return view('admin.candidate.edit',compact('candidate','posts'));
    }

    public function edit(Request $req, $id)
    {
        $candidate = Candidate::find($id);
        $postid = Post::find($req->input('postname'))->id;

        $candidate->post_id = $postid;
        $candidate->nepali_name = $req->input('nepname');
        $candidate->english_name = $req->input('engname');

        
        
        if ($req->hasFile('photo')) {
            $file = $req->file('photo');
            $image_one = uniqid().'.'.".png";
            $image = Image::make($file)->resize(250,185)->save('uploads/candidate/'.$image_one);
            $enc =  $image->encode('data-url');
            $candidate->image = $enc;

        }

        $candidate->save();

        $data = $req->input('name');
        $req->session()->flash('edited',$data);
        return redirect(route('admin.candidate.index'));

    }


    public function export(Request $req)
    {
        $fileName = 'candidates.csv';
        $candidates = Candidate::all();
     $posts = Post::all(); 
        $candidatedata = DB::table('candidates')->selectRaw('count(id) as candidate_count, post_id')->groupBy('post_id')->get();
        foreach ($posts as $post)
        {
            foreach($candidatedata as $candidatedatum){
                if($candidatedatum->candidate_count >= $post->max_count)
                {
                    $headers = array(
                    "Content-type"        => "text/csv",
                    "Content-Disposition" => "attachment; filename=$fileName",
                    "Pragma"              => "no-cache",
                    "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                    "Expires"             => "0"
                    );

                    $columns = array('S.no', 'Candidate ID', 'Post Name','Photo Id', 'Nepali Name', 'English Name','Photo');

                    $callback = function() use($candidates, $columns) {
                        $file = fopen('php://output', 'w');
                        fputcsv($file, $columns);
                        foreach ($candidates as $key=>$candidate) {


                            $pid = sprintf("%02d",$candidate['post_id']);
                            $canid = sprintf("%03d",$candidate['id']);

                            $row['S.no']  = $key+1;
                            $row['Candidate ID']  = $candidate->id;
                            $row['Post Name']    = $candidate->getPosts->post_name;
                            $row['Photo Id'] = $pid.$canid.".png";
                            $row['Nepali Name']    = $candidate->nepali_name;
                            $row['English Name']  = $candidate->english_name;
                            $row['Photo']  = $candidate->image;

                            fputcsv($file, array($row['S.no'], $row['Candidate ID'], $row['Post Name'],$row['Photo Id'], $row['Nepali Name'],$row['English Name'],$row['Photo']));
                        }

                        fclose($file);
                    };

                    return response()->stream($callback, 200, $headers);
                }
                else
                {
                    $data = $req->input('name');
                    $req->session()->flash('wrong');
                    return redirect()->back();
                }
            }
        }
        
    }
}
