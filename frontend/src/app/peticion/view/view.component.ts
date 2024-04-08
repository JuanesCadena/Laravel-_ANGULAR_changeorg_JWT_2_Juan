import { Component, OnInit } from '@angular/core';
import { PeticionService } from '../peticion.service';
import { ActivatedRoute, Router } from '@angular/router';
import { Peticion } from '../peticion';
    
@Component({
  selector: 'app-view',
  templateUrl: './view.component.html',
  styleUrls: ['./view.component.css']
})
export class ViewComponent implements OnInit {
     
  id!: number;
  peticion!: Peticion;
  image:string="";
  categoria:number=0;
  private serverURL = "http://localhost:8000/"
    
  /*------------------------------------------
  --------------------------------------------
  Created constructor
  --------------------------------------------
  --------------------------------------------*/
  constructor(
    public peticionService: PeticionService,
    private route: ActivatedRoute,
    private router: Router
   ) { }
    
  /**
   * Write code on Method
   *
   * @return response()
   */
  ngOnInit(): void {
    this.id = this.route.snapshot.params['peticionId'];
        
    this.peticionService.find(this.id).subscribe((data: any)=>{
      this.peticion = data;
   //   this.categoria=this.peticion.categoria.nombre;
     // this.image=this.serverURL+this.peticion.files[0].file_path;
      console.log(data);
    });
  }

  firma(id:number){
    this.peticionService.firmar(this.id).subscribe((data: any)=>{
      this.peticion=data;
      //this.categoria=this.peticion.categoria.nombre;
      this.image=this.serverURL+this.peticion.files[0].file_path
    });
  }
    
}