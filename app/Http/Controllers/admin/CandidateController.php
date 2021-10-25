<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\Post;
use DB;
use Image;
use ZipArchive;
use File;

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
        $candidate->save();

        $pid = sprintf("%02d",$postid);
        $canid = sprintf("%03d",$candidate['id']);

        $latestid = $candidate->id;
        $findid = Candidate::find($latestid);

        if ($req->hasFile('photo')) {
            $file = $req->file('photo');
            $image_one = $pid.$canid.".png";
            $filename = 'uploads/'.$image_one;
            $image = Image::make($file)->resize(600,600)->save($filename);
            $findid->image = $filename;       
        }
        $findid->save();
        $data = $req->input('nepname');
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

        $pid = sprintf("%02d",$postid);
        $canid = sprintf("%03d",$candidate['id']);

        if ($req->hasFile('photo')) {
            $file = $req->file('photo');
            $image_one = $pid.$canid.".png";
            $filename = 'uploads/'.$image_one;
            $image = Image::make($file)->resize(600,600)->save($filename);
            $candidate->image = $filename;       
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
        $candidatedata = DB::table('candidates')->selectRaw('count(candidates.id) as candidate_count, posts.max_count, post_id')->join('posts', 'candidates.post_id', '=', 'posts.id')->groupBy('post_id')->get();
       
        $count = 0;   
            foreach($candidatedata as $candidatedatum){
               
                if($candidatedatum->candidate_count >= $candidatedatum->max_count)
                {
                    $count++;
                    
                if($count == sizeof($candidatedata)) {
                    $headers = array(
                        "Content-type"        => "textl/csv",
                        "Content-Disposition" => "attachment;filename=$fileName",
                        "Pragma"              => "no-cache",
                        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                        "Expires"             => "0"
                        );
                    $columns = array('Candidate ID', 'Post Name','Photo Id', 'Nepali Name', 'English Name');

                    $callback = function() use($candidates, $columns) {
                        $file = fopen('php://output', 'w');
                        fputcsv($file, $columns);
                        foreach ($candidates as $key=>$candidate) {


                            $pid = sprintf("%02d",$candidate['post_id']);
                            $canid = sprintf("%03d",$candidate['id']);

                            $row['Candidate ID']  = $candidate->id;
                            $row['Post Name']    = $candidate->getPosts->post_name;
                            $row['Photo Id'] = $pid.$canid.".png";
                            $row['Nepali Name']    = $candidate->nepali_name;
                            $row['English Name']  = $candidate->english_name;
                            fputcsv($file, array($row['Candidate ID'], $row['Post Name'],$row['Photo Id'], $row['Nepali Name'],$row['English Name'],$row['Photo']));
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


    public function exportPhoto(Request $req)
    {
        $zip = new ZipArchive;
        $fileName = 'CandidatePhotos.zip';
        if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE) {
            $files = File::files(public_path('uploads'));
   
            foreach ($files as $key => $value) {
                $relativeNameInZipFile = basename($value);
                $zip->addFile($value, $relativeNameInZipFile);
            }
             
            $zip->close();
        }
        return response()->download(public_path($fileName));
    }


}
