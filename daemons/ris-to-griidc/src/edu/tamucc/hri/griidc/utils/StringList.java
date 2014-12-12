package edu.tamucc.hri.griidc.utils;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;

public class StringList {

	private List<String> items = null;
	
	public String stringSeparator = ConfigurationConstants.getDefaultStringListSeparator();
	
	public StringList() {
		this.items = new ArrayList<String>();
	}
	public StringList(String[] aa) {
		this();
		for(String s: aa) 
			this.items.add(s);
	}
	public StringList(String authors, String delimiter) {
		this();
		this.stringSeparator = delimiter;
		String[] aa = authors.split(this.stringSeparator);
		for(String s: aa) 
			this.items.add(s);
	}
	/**
	 * Separator is used when returning the list as a string.
	 * the Separator is inserted between each pair in the list.
	 * @return
	 */
	public String getSeparator() {
		return this.stringSeparator;
	}
	/**
	 * Separator is used when returning the list as a string.
	 * the Separator is inserted between each pair in the list.
	 */
	public void setSeparator(String itemStringSeparator) {
		this.stringSeparator = itemStringSeparator;
	}

	/**
	 * return the set of authors as an array of type String
	 * @return
	 */
	public String[] toArray() {
		String[] sa = new String[this.items.size()];
		return items.toArray(sa);
	}
	/**
	 * return all the items as one String with Separator separating
	 * @return
	 */
	public String toString() {
		String[] sa = this.toArray();
		StringBuffer sb = new StringBuffer();
		for(String s : sa) {
			if(sb.toString().length() > 0) sb.append(this.stringSeparator);
			sb.append(s);
		}
		return sb.toString();
	}
	/**
	 * put an item in the item list. in order of entry
	 * @param item
	 */
	public void addItem(String item) {
		this.items.add(item);
	}
	
	/**
	 * get an item matching target and return it.
	 * If there is not one in the list return null;
	 */
	
	public String getItem(String target) {
		Iterator<String>it = this.items.iterator();
		while(it.hasNext()) {
			String s = it.next();
			if(s.equals(target)) return s;
		}
		return null;
	}
	/**
	 * search the list for an item that contains the target.
	 * The first item found is returned.
	 * If none is found null is returned.
	 * @param target
	 * @return
	 */
	public String findContains(String target) {
		Iterator<String>it = this.items.iterator();
		while(it.hasNext()) {
			String s = it.next();
			if(s.contains(target)) return s;
		}
		return null;
	}
	
	public int size() {
		return this.items.size();
	}
}
